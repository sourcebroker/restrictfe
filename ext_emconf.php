<?php

$EM_CONF[$_EXTKEY] = [
    'title'            => 'Restrict access for staging and prod instances',
    'description'      => 'This extension blocks access to frontend and allows to show it only to some defined exception\'s like if the request is from an authorized backend user, has specific IP, header, domain, language or GET/POST vars. Useful to protect your staging and production instances.',
    'author'           => 'SourceBroker Team',
    'author_email'     => 'office@sourcebroker.dev',
    'author_company'   => 'SourceBroker',
    'category'         => 'fe',
    'version'          => '10.0.4',
    'state'            => 'stable',
    'constraints'      => [
        'depends' => [
            'typo3' => '10.4.0-11.99.999',
        ],
        'conflicts' => [],
        'suggests'  => [],
    ],
];
