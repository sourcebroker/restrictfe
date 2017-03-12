<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Allows to see FE only for logged BE users.',
    'description' => 'When you have stages like LOCAL/DEV/LIVE you may want DEV to be access restricted. One way is to generate htaccess for that. The second is to use this extension which allows only logged BE user to see FE.',
    'category' => 'fe',
    'version' => '5.0.0',
    'state' => 'stable',
    'uploadfolder' => false,
    'createDirs' => '',
    'clearcacheonload' => 0,
    'author' => 'SourceBroker Team',
    'author_email' => 'office@sourcebroker.net',
    'author_company' => 'SourceBroker',
    'constraints' => [
        'depends' => [
            'typo3' => '6.2.0-8.6.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
