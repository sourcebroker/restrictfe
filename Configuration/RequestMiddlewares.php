<?php /** @noinspection PhpFullyQualifiedNameUsageInspection */

return [
    'frontend' => [
        'sourcebroker/restrictfe/check' => [
            'target' => \SourceBroker\Restrictfe\Middleware\RequestCheck::class,
            'after' => [
                'typo3/cms-frontend/prepare-tsfe-rendering',
            ],
            'before' => [
                'typo3/cms-frontend/shortcut-and-mountpoint-redirect',
            ],
        ],
        'sourcebroker/restrictfe/backend-user-authentication' => [
            'target' => \SourceBroker\Restrictfe\Middleware\BackendUserCheck::class,
            'after' => [
                'typo3/cms-frontend/backend-user-authentication',
            ],
            'before' => [
                'typo3/cms-frontend/authentication',
            ]
        ],
    ],
];
