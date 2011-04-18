<?php
	if (!defined ('TYPO3_MODE'))    die ('Access denied.');

	$tempColumns = Array (
		'tx_t3orgcomments_feuser' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:t3org_comments/locallang_db.xml:tx_comments_comments.tx_t3orgcomments_feuser',
			'config' => Array (
				'type' => 'group',
				'internal_type' => 'db',
				'allowed' => 'fe_users',
				'size' => 1,
				'minitems' => 1,
				'maxitems' => 1,
			)
		),
	);


	t3lib_div::loadTCA('tx_comments_comments');
	$TCA['tx_comments_comments']['columns']['email']['config']['type'] = 'passthrough';
	$TCA['tx_comments_comments']['columns']['firstname']['config']['type'] = 'passthrough';
	$TCA['tx_comments_comments']['columns']['firstname']['config']['eval'] = 'trim';
	$TCA['tx_comments_comments']['columns']['lastname']['config']['type'] = 'passthrough';
	$TCA['tx_comments_comments']['columns']['location']['config']['type'] = 'passthrough';
	$TCA['tx_comments_comments']['columns']['homepage']['config']['type'] = 'passthrough';
	$TCA['tx_comments_comments']['columns']['remote_addr']['config']['readOnly'] = 1;
	$TCA['tx_comments_comments']['columns']['remote_addr']['config']['size'] = 15;

	t3lib_extMgm::addTCAcolumns('tx_comments_comments',$tempColumns,1);
	t3lib_extMgm::addToAllTCAtypes('tx_comments_comments','tx_t3orgcomments_feuser;;;;1-1-1', '', 'after:approved');
	t3lib_extMgm::addLLrefForTCAdescr('tx_comments_comments','EXT:t3org_comments/locallang_csh_tx_comments_comments.xml');

?>
