<?php

declare(strict_types=1);

/** @noinspection PhpUndefinedVariableInspection */
$EM_CONF[$_EXTKEY] = [
    'title' => 'GraphQL Test Extension 3',
    'description' => 'Graphql3 does not provide much out of the box. This extension was made for testing.',
    'author' => 'Rozbeh Chiryai Sharahi',
    'author_email' => 'rozbeh.sharahi+graphql3@gmail.com',
    'state' => 'alpha',
    'clearCacheOnLoad' => 0,
    'version' => '1.0.0',
    'constraints' => [
        'depends' => ['graphql3' => '0-99'],
        'conflicts' => [],
        'suggests' => [],
    ],
];
