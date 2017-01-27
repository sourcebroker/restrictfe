<?php
defined('TYPO3_MODE') or die();

// Extension will not work unless you set the $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['restrictfe']['enable'] - preferably at DEV instance
if ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['restrictfe']['enable']) {
    $TYPO3_CONF_VARS['SC_OPTIONS']['tslib/class.tslib_fe.php']['checkAlternativeIdMethods-PostProc'][]
        = 'SourceBroker\Restrictfe\Main->redirectCheckForLoggedBeUser';
}
