<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Allows to see FE only for logged BE users.',
    'description' => 'When you have stages like LOCAL/DEV/LIVE you may want DEV to be access restricted. One way is to generate htaccess for that. The second is to use this extension which allows only logged BE user to see FE.',
    'author' => 'SourceBroker Team',
    'author_email' => 'office@sourcebroker.net',
    'author_company' => 'SourceBroker',
    'category' => 'fe',
    'version' => '6.0.0',
    'shy' => '',
    'priority' => '',
    'module' => '',
    'state' => 'stable',
    'internal' => '',
    'uploadfolder' => '0',
    'createDirs' => '',
    'modify_tables' => '',
    'lockType' => '',
    'clearCacheOnLoad' => 0,
    'constraints' => [
        'depends' => [
            'typo3' => '6.2.0-8.6.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
    'autoload' => [
        'psr-4' => [
            'SourceBroker\\Restrictfe\\' => 'Classes',
        ]
    ]
];
