<?php


if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

$TYPO3_CONF_VARS['EXTCONF']['comments']['processSubmission'][$_EXTKEY] = 'EXT:' . $_EXTKEY . '/hooks/class.tx_t3orgcomments_hooks.php:&tx_t3orgcomments_hooks->processSubmission';

$TYPO3_CONF_VARS['EXTCONF']['comments']['mergeConfiguration'][$_EXTKEY] = 'EXT:' . $_EXTKEY . '/hooks/class.tx_t3orgcomments_hooks.php:&tx_t3orgcomments_hooks->mergeConfiguration';

$TYPO3_CONF_VARS['EXTCONF']['comments']['comments_getComments'][$_EXTKEY] = 'EXT:' . $_EXTKEY . '/hooks/class.tx_t3orgcomments_hooks.php:&tx_t3orgcomments_hooks->comments_getComments';


?>
