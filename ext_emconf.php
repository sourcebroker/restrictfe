<?php

$EM_CONF[$_EXTKEY] = [
    'title'            => 'Restrict access for staging and prod instances',
    'description'      => 'This extension blocks access to frontend and allows to show it only to some defined exception\'s like if the request is from an authorized backend user, has specific IP, header, domain, language or GET/POST vars. Useful to protect your staging and production instances.',
    'author'           => 'Inscript Team',
    'author_email'     => 'office@inscript.dev',
    'author_company'   => 'Inscript',
    'category'         => 'fe',
    'version'          => '11.0.0',
    'state'            => 'stable',
    'constraints'      => [
        'depends' => [
            'typo3' => '11.5.0-12.5.999',
        ],
        'conflicts' => [],
        'suggests'  => [],
    ],
];
