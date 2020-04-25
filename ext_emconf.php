<?php

$EM_CONF[$_EXTKEY] = [
    'title'            => 'Restrict access for staging and prod instances',
    'description'      => 'This extension blocks access to frontend and allows to show it only to some defined exception\'s like if the request is from an authorized backend user, has specific IP, header, domain, language or GET/POST vars. Useful to protect your staging and production instances.',
    'author'           => 'SourceBroker Team',
    'author_email'     => 'office@sourcebroker.net',
    'author_company'   => 'SourceBroker',
    'category'         => 'fe',
    'version'          => '9.0.0',
    'shy'              => '',
    'priority'         => '',
    'module'           => '',
    'state'            => 'stable',
    'internal'         => '',
    'uploadfolder'     => '0',
    'createDirs'       => '',
    'modify_tables'    => '',
    'lockType'         => '',
    'clearCacheOnLoad' => 0,
    'constraints'      => [
        'depends' => [
            'typo3' => '8.7.0-10.4.999',
        ],
        'conflicts' => [],
        'suggests'  => [],
    ],
];
