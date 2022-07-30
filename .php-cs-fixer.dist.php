<?php

$finder = PhpCsFixer\Finder::create()
    ->in([
        __DIR__.'/Classes',
        __DIR__.'/Tests'
    ])
;

$config = new PhpCsFixer\Config();
return $config->setRules([
    '@Symfony' => true,
    'multiline_whitespace_before_semicolons' => ['strategy' => 'new_line_for_chained_calls']
])->setFinder($finder);