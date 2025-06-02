<?php

$EM_CONF[$_EXTKEY] = [
    'title'            => 'Restrict access for staging and prod instances',
    'description'      => 'This extension blocks access to frontend and allows to show it only to some defined exception\'s like if the request is from an authorized backend user, has specific IP, header, domain, language or GET/POST vars. Useful to protect your staging and production instances.',
    'author'           => 'Inscript Team',
    'author_email'     => 'office@inscript.dev',
    'author_company'   => 'Inscript',
    'category'         => 'fe',
    'version'          => '12.0.1',
    'state'            => 'stable',
    'constraints'      => [
        'depends' => [
            'typo3' => '11.5.0-13.4.999',
        ],
        'conflicts' => [],
        'suggests'  => [],
    ],
];
