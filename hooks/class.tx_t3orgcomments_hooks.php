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

}

?>
