<?php 

/**
 * xclass to fix a bug where clearing the cache after a commit throws an exception
 * 
 * Changes where done in lines 133 to 142
 * 
 * @author Christian Zenker <christian.zenker@599media.de>
 */
class ux_tx_comments_pi1 extends tx_comments_pi1 {
	/**
	 * Processes form submissions.
	 *
	 * @return	void
	 */
	function processSubmission() {
		if ($this->piVars['submit'] && $this->processSubmission_validate()) {
			$external_ref = $this->foreignTableName . '_' . $this->externalUid;
			// Create record
			$record = array(
				'pid' => intval($this->conf['storagePid']),
				'external_ref' => $external_ref,	// t3lib_loaddbgroup should be used but it is very complicated for FE... So we just do it with brute force.
				'external_prefix' => trim($this->conf['externalPrefix']),
				'firstname' => trim($this->piVars['firstname']),
				'lastname' => trim($this->piVars['lastname']),
				'email' => trim($this->piVars['email']),
				'location' => trim($this->piVars['location']),
				'homepage' => trim($this->piVars['homepage']),
				'content' => trim($this->piVars['content']),
				'remote_addr' => t3lib_div::getIndpEnv('REMOTE_ADDR'),
			);

			// Call hook for additional fields in record (by Frank Naegler)
			if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['comments']['processSubmission'])) {
				foreach ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['comments']['processSubmission'] as $userFunc) {
					$params = array(
						'record' => $record,
						'pObj' => &$this,
					);
					if (($newRecord = t3lib_div::callUserFunction($userFunc, $params, $this))) {
						$record = $newRecord;
					}
				}
			}

			// Check for double post
			$double_post_check = md5(implode(',', $record));
			if ($this->conf['preventDuplicatePosts']) {
				list($info) = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('COUNT(*) AS t', 'tx_comments_comments',
						$this->where_dpck . ' AND crdate>=' . (time() - 60*60) . ' AND double_post_check=' . $GLOBALS['TYPO3_DB']->fullQuoteStr($double_post_check, 'tx_comments_comments'));
			}
			else {
				$info = array('t' => 0);
			}

			if ($info['t'] > 0) {
				// Double post!
				$this->formTopMessage = $this->pi_getLL('error.double.post');
			}
			else {
				$isSpam = $this->processSubmission_checkTypicalSpam();
				$cutOffPoint = $this->conf['spamProtect.']['spamCutOffPoint'] ? $this->conf['spamProtect.']['spamCutOffPoint'] : $isSpam + 1;
				if ($isSpam < $cutOffPoint) {
					$isApproved = !$isSpam && intval($this->conf['spamProtect.']['requireApproval'] ? 0 : 1);

					// Add rest of the fields
					$record['crdate'] = $record['tstamp'] = time();
					$record['approved'] = $isApproved;
					$record['double_post_check'] = $double_post_check;

					// Insert comment record
					$GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_comments_comments', $record);
					$newUid = $GLOBALS['TYPO3_DB']->sql_insert_id();

					// Update reference index. This will show in theList view that someone refers to external record.
					$refindex = t3lib_div::makeInstance('t3lib_refindex');
					/* @var $refindex t3lib_refindex */
					$refindex->updateRefIndexTable('tx_comments_comments', $newUid);

					// Insert URL (if exists)
					if ($this->conf['advanced.']['enableUrlLog'] && $this->hasValidItemUrl()) {
						// See if exists
						$rows = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('uid,url', 'tx_comments_urllog',
										'external_ref=' . $GLOBALS['TYPO3_DB']->fullQuoteStr($external_ref, 'tx_comments_urllog') .
										$this->cObj->enableFields('tx_comments_urllog'));
						if (count($rows) == 0) {
							$record = array(
								'crdate' => time(),
								'tstamp' => time(),
								'pid' => intval($this->conf['storagePid']),
								'external_ref' => $external_ref,
								'url' => $this->piVars['itemurl'],
							);
							$GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_comments_urllog', $record);
							$refindex->updateRefIndexTable('tx_comments_urllog', $GLOBALS['TYPO3_DB']->sql_insert_id());
						}
						elseif ($rows[0]['url'] != $this->piVars['itemurl'] && !$this->isNoCacheUrl($this->piVars['itemurl'])) {
							$record = array(
								'tstamp' => time(),
								'url' => $this->piVars['itemurl'],
							);
							$GLOBALS['TYPO3_DB']->exec_UPDATEquery('tx_comments_urllog', 'uid=' . $rows[0]['uid'], $record);
						}
					}

					// Set cookies
					foreach (array('firstname', 'lastname', 'email', 'location', 'homepage') as $field) {
						setcookie($this->prefixId . '_' . $field, $this->piVars[$field], time() + 365*24*60*60, '/');
					}

					// See what to do next
					if (!$isApproved) {
						// Show message
						$this->formTopMessage = $this->pi_getLL('requires.approval');
						$this->sendNotificationEmail($newUid, $isSpam);
					}
					else {

						// Call hook for custom actions (requested by Cyrill Helg)
						if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['comments']['processValidComment'])) {
							foreach($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['comments']['processValidComment'] as $userFunc) {
								$params = array(
									'pObj' => &$this,
									'uid' => intval($newUid),
								);
								t3lib_div::callUserFunction($userFunc, $params, $this);
							}
						}

						// Clear cache
						$clearCache = t3lib_div::trimExplode(',', $this->conf['additionalClearCachePages'], true);
						$clearCache[] = $GLOBALS['TSFE']->id;
## changes start here
// the following lines where removed
// - 					$tce = t3lib_div::makeInstance('t3lib_TCEmain');
// -					/* @var $tce t3lib_TCEmain */
// -					foreach (array_unique($clearCache) as $pid) {
// - 						$tce->clear_cacheCmd($pid);
// -					}
// the following lines where added
						$GLOBALS['TSFE']->clearPageCacheContent_pidList($clearCache);
## changes end here 

						// Go to first/last page using redirect
						$queryParams = $_GET;
						foreach (array('no_cache', 'cHash') as $var) {
							unset($queryParams[$var]);
						}
						if ($this->conf['advanced.']['reverseSorting']) {
							unset($queryParams[$this->prefixId]['page']);
						}
						else {
							$rpp = intval($this->conf['advanced.']['commentsPerPage']);
							list($info) = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('COUNT(*) AS t',
									'tx_comments_comments', $this->where);
							$page = intval($info['t']/$rpp) + (($info['t'] % $rpp) ? 1 : 0) - 1;
							if ($page > 0) {
								$queryParams[$this->prefixId]['page'] = $page;
							}
							else {
								unset($queryParams[$this->prefixId]['page']);
							}
						}
						$redirectLink = $this->cObj->typoLink_URL(array(
							'parameter' => $GLOBALS['TSFE']->id,
							'additionalParams' => t3lib_div::implodeArrayForUrl('', $queryParams),
							'useCacheHash' => true,
						));
						@ob_end_clean();
						header('Location: ' . t3lib_div::locationHeaderUrl($redirectLink));
						exit;
					}
				}
				else {
					// Spam cut off point reached
					$this->formTopMessage = $this->pi_getLL('error_too_many_spam_points');
				}
			}
		}
		if ($this->formTopMessage) {
			$this->formTopMessage = $this->cObj->substituteMarkerArray(
				$this->cObj->getSubpart($this->templateCode, '###FORM_TOP_MESSAGE###'), array(
					'###MESSAGE###' => $this->formTopMessage,
					'###SITE_REL_PATH###' => t3lib_extMgm::siteRelPath('comments')
				)
			);
		}
	}
}

?>