<?php

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

$tempColumns = [
    'tx_restrictfe_clearbesession' => [
        'exclude' => 1,
        'label' => 'Clear BE session after login',
        'config' => [
            'type' => 'check',
            'default' => 0,
        ],
    ],
];

ExtensionManagementUtility::addTCAcolumns('be_users', $tempColumns);
ExtensionManagementUtility::addToAllTCAtypes('be_users',
    '--div--;Restrictfe,tx_restrictfe_clearbesession');
