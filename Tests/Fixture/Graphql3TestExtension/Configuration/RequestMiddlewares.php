<?php

use RozbehSharahi\Graphql3\Middleware\GraphqlRequestMiddleware;
use RozbehSharahi\Graphql3TestExtension\Middleware\GraphqlRegistrationMiddleware;

return [
    'frontend' => [
        GraphqlRegistrationMiddleware::class => [
            'after' => ['typo3/cms-frontend/site'],
            'before' => [GraphqlRequestMiddleware::class],
            'target' => GraphqlRegistrationMiddleware::class,
        ],
    ],
];
