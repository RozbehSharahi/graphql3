<?php

$finder = PhpCsFixer\Finder::create()
    ->in([
        __DIR__ . '/Classes',
        __DIR__ . '/Tests'
    ]);

$config = new PhpCsFixer\Config();
return $config
    ->setRules([
        '@Symfony' => true,
        'declare_strict_types' => true,
        'multiline_whitespace_before_semicolons' => ['strategy' => 'new_line_for_chained_calls'],
    ])
    ->setRiskyAllowed(true)
    ->setFinder($finder);