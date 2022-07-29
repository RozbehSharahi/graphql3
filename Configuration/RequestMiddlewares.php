<?php

use RozbehSharahi\Graphql3\Middleware\GraphqlRequestMiddleware;

return [
    'frontend' => [
        GraphqlRequestMiddleware::class => [
            'after' => ['typo3/cms-frontend/base-redirect-resolver'],
            'before' => ['typo3/cms-frontend/static-route-resolver'],
            'target' => GraphqlRequestMiddleware::class
        ]
    ]
];