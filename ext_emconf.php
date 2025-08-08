<?php

/** @noinspection PhpUndefinedVariableInspection */
$EM_CONF[$_EXTKEY] = [
    'title' => 'GraphQL3',
    'description' => 'A graphql extension for TYPO3',
    'author' => 'Rozbeh Chiryai Sharahi',
    'author_email' => 'rozbeh.sharahi+graphql3@gmail.com',
    'state' => 'alpha',
    'clearCacheOnLoad' => 0,
    'version' => '2.0.0',
    'constraints' => [
        'depends' => [
            'typo3' => '12.4.0-13.99.99',
        ],
        'conflicts' => [
        ],
        'suggests' => [
        ],
    ],
];