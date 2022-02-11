<?php

if (PHP_SAPI !== 'cli') {
    die('This script supports command line usage only. Please check your command.');
}

$header = <<<EOF
This file is part of the package crell/planedo-bundle.
For the full copyright and license information, please read the
LICENSE file that was distributed with this source code.
EOF;

return (new PhpCsFixer\Config())
    ->setRiskyAllowed(false)
    ->setRules([
        '@Symfony' => true,
        'array_syntax' => ['syntax' => 'short'],
        'concat_space' => ['spacing' => 'one'],
        'header_comment' => [
            'header' => $header
        ],
        'no_superfluous_phpdoc_tags' => false,
        'declare_strict_types' => true,
        'protected_to_private' => false,
        'general_phpdoc_tag_rename' => false,
        'phpdoc_align' => false,
    ])
    ->setFinder(
        PhpCsFixer\Finder::create()
            ->in(['src', 'tests'])
    );
