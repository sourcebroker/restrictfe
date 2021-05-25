<?php /** @noinspection PhpFullyQualifiedNameUsageInspection */

return [
    'frontend' => [
        'sourcebroker/restrictfe/backend-user-check' => [
            'target' => \SourceBroker\Restrictfe\Middleware\BackendUserCheck::class,
            'after' => [
                'typo3/cms-frontend/backend-user-authentication',
            ],
            'before' => [
                'typo3/cms-frontend/authentication',
            ]
        ],
        'sourcebroker/restrictfe/request-check' => [
            'target' => \SourceBroker\Restrictfe\Middleware\RequestCheck::class,
            'after' => [
                'typo3/cms-frontend/prepare-tsfe-rendering',
            ],
            'before' => [
                'typo3/cms-frontend/shortcut-and-mountpoint-redirect',
            ],
        ],
    ],
    'backend' => [
        'sourcebroker/restrictfe/redirect-backend' => [
            'target' => \SourceBroker\Restrictfe\Middleware\BackendRedirect::class,
            'after' => [
                'typo3/cms-backend/authentication',
            ],
            'before' => [
                'typo3/cms-backend/output-compression',
            ],
        ],
    ],
];
