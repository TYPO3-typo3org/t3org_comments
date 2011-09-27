<?php


if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

$TYPO3_CONF_VARS['EXTCONF']['comments']['processSubmission'][$_EXTKEY] = 'EXT:' . $_EXTKEY . '/hooks/class.tx_t3orgcomments_hooks.php:&tx_t3orgcomments_hooks->processSubmission';

$TYPO3_CONF_VARS['EXTCONF']['comments']['mergeConfiguration'][$_EXTKEY] = 'EXT:' . $_EXTKEY . '/hooks/class.tx_t3orgcomments_hooks.php:&tx_t3orgcomments_hooks->mergeConfiguration';

$TYPO3_CONF_VARS['EXTCONF']['comments']['comments_getComments'][$_EXTKEY] = 'EXT:' . $_EXTKEY . '/hooks/class.tx_t3orgcomments_hooks.php:&tx_t3orgcomments_hooks->comments_getComments';

$TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/comments/pi1/class.tx_comments_pi1.php'] = t3lib_extMgm::extPath($_EXTKEY).'/xclasses/class.ux_tx_comments_pi1.php';

?>
