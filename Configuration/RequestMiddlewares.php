<?php

use RozbehSharahi\Graphql3\Middleware\GraphqlRequestMiddleware;

return [
    'frontend' => [
        GraphqlRequestMiddleware::class => [
            'after' => ['typo3/cms-frontend/site'],
            'before' => ['typo3/cms-frontend/backend-user-authentication'],
            'target' => GraphqlRequestMiddleware::class
        ]
    ]
];