<?php

// Hook into "postUserLookUp" to store restrictfe cookie after successful BE login
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_userauth.php']['postUserLookUp'][]
    = 'SourceBroker\Restrictfe\BackendUserAuthentication->storeRestrictfeCookieAfterSuccessfulBeLogin';

// We hook into "settingLanguage_postProcess" because in this hook we will have all info need to do all conditions.
// We will decide here to show fronted or not.
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_fe.php']['settingLanguage_postProcess'][]
    = 'SourceBroker\Restrictfe\RestrictFrontend->checkExceptionsAndBlockFrontendIfNeeded';
