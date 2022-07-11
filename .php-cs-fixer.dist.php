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
])->setFinder($finder);