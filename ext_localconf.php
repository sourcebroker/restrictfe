<?php

if (TYPO3_MODE == 'FE') {
    // hook into settingLanguage_postProcess to have info about sys_language_uid to have ability to do conditions with language
    $TYPO3_CONF_VARS['SC_OPTIONS']['tslib/class.tslib_fe.php']['settingLanguage_postProcess'][]
        = 'SourceBroker\Restrictfe\Restrict->restrictFrontend';
}
