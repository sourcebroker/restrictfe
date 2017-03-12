<?php

if (TYPO3_MODE == 'FE') {
    $TYPO3_CONF_VARS['SC_OPTIONS']['tslib/class.tslib_fe.php']['checkAlternativeIdMethods-PostProc'][]
        = 'SourceBroker\Restrictfe\Main->redirectCheckForLoggedBeUser';
}
