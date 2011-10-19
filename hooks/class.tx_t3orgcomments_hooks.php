<?php

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2011 Soren Malling <soren@sorenmalling.me>
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

class tx_t3orgcomments_hooks {

	/**
	 * After submit, but before saving to DB, we defines a value for the frontend user field
	 *
	 * @param array $params Parameters for current part of the hook
	 *
	 * @return void
	 */

	public function processSubmission($params) {
		$params['record']['tx_t3orgcomments_feuser'] = (int) $GLOBALS['TSFE']->fe_user->user['uid'];
		
		return $params['record'];
	}

	/**
	 * Replacing markers with user information, to be shown on the list
	 *
	 * @param array $params Parameters for current part of the hook
	 *
	 * @return void
	 */

	function comments_getComments($params) {

		$comment = $GLOBALS['TYPO3_DB']->exec_SELECTgetSingleRow(
			'tx_t3orgcomments_feuser',
			'tx_comments_comments',
			'uid=' . (int) $params['row']['uid']
		);

		$author = $GLOBALS['TYPO3_DB']->exec_SELECTgetSingleRow(
			'uid,name,tx_t3ouserimage_img_hash',
			'fe_users',
			'uid=' . (int) $comment['tx_t3orgcomments_feuser']
		);

		$params['markers']['###NAME###'] = $author['name'];
		$params['markers']['###IMAGE###'] = "/fileadmin/" . $author['tx_t3ouserimage_img_hash'] . "-big.jpg";

		return $params['markers'];
	}

	/**
	 * If there is no logged in frontend user, we will use a template with no form, only list
	 *
	 * @param array $record Configuration record of current cObj
	 * @param array $pObj Reference to parent object, holding the configuration we are overriding
	 *
	 * @return void
	 */

	public function mergeConfiguration($record, &$pObj) {
		if($GLOBALS['TSFE']->fe_user->user == false) {
			$pObj->conf['templateFile'] = 'EXT:t3org_comments/res/no_form.html';
		} else {
			$pObj->conf['templateFile'] = 'EXT:t3org_comments/res/form.html';
		}
	}
	
	/**
	 * add marker for all comments
	 * 
	 * @param array $params
	 * @param tx_comments_pi1 $pObj
	 */
	public function addCommentMarkers($params, &$pObj) {
		$markers = $params['markers'];
		$template = $params['template'];
		
		// display <hr /> only if there are comments
		$countRecords = intval($GLOBALS['TYPO3_DB']->exec_SELECTcountRows(
			'*',
			'tx_comments_comments',
			$pObj->where
		));
		
		$pObj->cObj->cObjGetSingle('LOAD_REGISTER', array(
			't3o_num_comments' => $countRecords
		));
		
		$markers['###T3O_TITLE###'] = $pObj->cObj->cObjGetSingle($pObj->conf['t3o_title'],$pObj->conf['t3o_title.']);
		
		$pObj->cObj->cObjGetSingle('RESTORE_REGISTER', array());
		
		return $markers;
	}
	
	
	
	/**
	 * modify TOP_MESSAGE marker to get the look and feel of other flash messages on typo3.org
	 * 
	 * @param array $params
	 * @param tx_comments_pi1 $pObj
	 * @param string $template
	 * @param array $markers
	 * @author Christian Zenker <christian.zenker@599media.de>
	 * @return array markers
	 */
	public function addFormMarkers($params, &$pObj) {
		$markers = $params['markers'];
		$template = $params['template'];
		
		// wrap top message in according containers (warning, error, ok)
		$markers['###TOP_MESSAGE###'] = $this->getTopMessage($markers['###TOP_MESSAGE###'], $pObj);
		
		// display <hr /> only if there are comments
		$countRecords = intval($GLOBALS['TYPO3_DB']->exec_SELECTcountRows(
			'*',
			'tx_comments_comments',
			$pObj->where
		));
		
		$pObj->cObj->cObjGetSingle('LOAD_REGISTER', array(
			't3o_num_comments' => $countRecords
		));
		
		$markers['###T3O_HR###'] = $pObj->cObj->cObjGetSingle($pObj->conf['t3o_hr'],$pObj->conf['t3o_hr.']);
		$markers['###T3O_PLEASELOGIN###'] = $pObj->cObj->cObjGetSingle($pObj->conf['t3o_pleaselogin'],$pObj->conf['t3o_pleaselogin.']);
		
		$pObj->cObj->cObjGetSingle('RESTORE_REGISTER', array());
		
		return $markers;
	}
	
	/**
	 * get the message shown on top of the form wrapped in the correct container
	 * 
	 * @param string $msg
	 * @param tx_comments_pi1 $pObj
	 */
	protected function getTopMessage($msg, $pObj) {
		if(empty($msg)) {
			if(count($pObj->formValidationErrors) > 0) {
				// if: errors in the form 
				$msg = $pObj->cObj->stdWrap($pObj->pi_getLL('t3oerror.validation'), $pObj->conf['t3o_flashMessage_errorWrap.']);
			} elseif(t3lib_FlashMessageQueue::getAllMessages()) {
				// if: form was submitted and is valid
				$msg = '';
				foreach(t3lib_FlashMessageQueue::getAllMessagesAndFlush() as $message) {
					$wrapPath = $message->getSeverity() === t3lib_FlashMessage::OK ?
						't3o_flashMessage_okWrap.' :
						($message->getSeverity() === t3lib_FlashMessage::ERROR ? 't3o_flashMessage_okWrap.' : 't3o_flashMessage_noticeWrap.')
					;
					$msg .= $pObj->cObj->stdWrap($message, $pObj->conf[$wrapPath]);
				}
			}
			// else: form is just displayed
		} elseif($msg === $pObj->pi_getLL('error.double.post')) {
			// if: error because this comment was already posted
			$msg = $pObj->cObj->stdWrap($msg, $pObj->conf['t3o_flashMessage_errorWrap.']);
		} elseif($msg === $pObj->pi_getLL('requires.approval')) {
			// if: comment needs to be approved
			$msg = $pObj->cObj->stdWrap($msg, $pObj->conf['t3o_flashMessage_noticeWrap.']);
		} elseif($msg === $pObj->pi_getLL('error_too_many_spam_points')) {
			// if: error because of too many spam points
			$msg = $pObj->cObj->stdWrap($msg, $pObj->conf['t3o_flashMessage_errorWrap.']);
		}
		
		return $msg;
	}
	
	/**
	 * add a flash message to the queue if comment has been sucessfully commited
	 * 
	 * @param array $params
	 * @param tx_comments_pi1 $pObj
	 * @author Christian Zenker <christian.zenker@599media.de>
	 */
	public function processValidComment($params, $pObj) {		
		$message = t3lib_div::makeInstance(
		    't3lib_FlashMessage',
		    $pObj->pi_getLL('t3othankyou.msg'),
		    '',
		    t3lib_FlashMessage::OK,
		    TRUE // store message in session
		);
		t3lib_FlashMessageQueue::addMessage($message);
	}

}

?>
