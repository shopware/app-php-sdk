<?php
return (new PhpCsFixer\Config())
    ->setRiskyAllowed(true)
    ->setRules([
        '@PSR12' => true,
        '@PSR12:risky' => true,
    ])
    ->setFinder(PhpCsFixer\Finder::create()
        ->exclude('vendor')
        ->in(__DIR__)
    )
    ;