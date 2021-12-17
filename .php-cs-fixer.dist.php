<?php

$finder = PhpCsFixer\Finder::create()
    ->in([
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ])
;

$config = new PhpCsFixer\Config();

return $config->setRules([
        '@PSR1' => true,
        '@PSR2' => true,
        'no_unused_imports' => true,
        'ordered_imports' => true,
        'single_blank_line_before_namespace' => true,
    ])
    ->setFinder($finder)
;
