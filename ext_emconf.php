<?php

########################################################################
# Extension Manager/Repository config file for ext "t3org_comments".
#
# Auto generated 18-04-2011 22:41
#
# Manual updates:
# Only the data in the array - everything else is removed by next
# writing. "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'Comments for TYPO3.org',
	'description' => 'Integration of EXT:comments to be used for TYPO3.org (news, video etc)',
	'category' => 'fe',
	'author' => 'Soren Malling',
	'author_email' => 'soren@sorenmalling.me',
	'shy' => '',
	'dependencies' => 'comments',
	'conflicts' => '',
	'priority' => '',
	'module' => '',
	'state' => 'alpha',
	'internal' => '',
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => '',
	'clearCacheOnLoad' => 0,
	'lockType' => '',
	'author_company' => '',
	'version' => '0.0.0',
	'constraints' => array(
		'depends' => array(
			'comments' => '',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'_md5_values_when_last_written' => 'a:11:{s:9:"ChangeLog";s:4:"4f70";s:10:"README.txt";s:4:"ee2d";s:12:"ext_icon.gif";s:4:"1bdc";s:17:"ext_localconf.php";s:4:"4a32";s:14:"ext_tables.php";s:4:"3763";s:14:"ext_tables.sql";s:4:"a837";s:19:"doc/wizard_form.dat";s:4:"5158";s:20:"doc/wizard_form.html";s:4:"f56c";s:38:"hooks/class.tx_t3orgcomments_hooks.php";s:4:"7994";s:13:"res/form.html";s:4:"83e9";s:16:"res/no_form.html";s:4:"44c5";}',
	'suggests' => array(
	),
);

?>