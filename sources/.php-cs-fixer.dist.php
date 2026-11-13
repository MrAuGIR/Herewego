<?php

declare(strict_types=1);

$finder = (new PhpCsFixer\Finder())
    ->in([__DIR__.'/src', __DIR__.'/tests', __DIR__.'/config', __DIR__.'/migrations', __DIR__.'/bin'])
    ->append([__FILE__])
    ->notPath([
        'bundles.php',
        'reference.php',
        'secrets',
    ])
;

return (new PhpCsFixer\Config())
    ->setRiskyAllowed(true)
    ->setRules([
        '@Symfony' => true,
        '@Symfony:risky' => true,
        'declare_strict_types' => true,
        'ordered_imports' => ['sort_algorithm' => 'alpha'],
        'phpdoc_to_comment' => false,
    ])
    ->setCacheFile(__DIR__.'/var/.php-cs-fixer.cache')
    ->setFinder($finder)
;
