<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Restrict access for staging and production instances',
    'description' => 'When you have staging instances you may want them to be access restricted. One way is to generate htaccess passwords - the second is to use this ext.',
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
