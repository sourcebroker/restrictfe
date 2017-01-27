<?php
defined('TYPO3_MODE') or die();

$tempColumns = [
    'tx_restrictfe_clearbesession' => [
        'exclude' => 1,
        'label' => 'Clear BE session after login',
        'config' => [
            'type' => 'check',
            'default' => 0
        ]
    ],
];

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('be_users', $tempColumns, 1);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('be_users', 'tx_restrictfe_clearbesession');
