<?php

if (TYPO3_MODE === 'FE') {
    // Hook into "postBeUser" to store info about Backend User Object because later when we are in hook
    // "settingLanguage_postProcess" this Backend User Object can be already unset (because Backend User
    // can have no access to page tree).
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/index_ts.php']['postBeUser'][]
        = 'SourceBroker\Restrictfe\Restrict->storeBackendUserRow';

    // We hook into "settingLanguage_postProcess" because in this hook we will have all info need to decide
    // if we can show frontend or not.
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_fe.php']['settingLanguage_postProcess'][]
        = 'SourceBroker\Restrictfe\Restrict->restrictFrontend';
}
