<?php

declare(strict_types=1);

/*
 * This file is part of the package crell/planedo-bundle.
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

$container->loadFromExtension('doctrine', [
    'dbal' => [
        'driver' => 'pdo_sqlite',
        'path' => '%kernel.cache_dir%/test_database.sqlite',
    ],
    'orm' => [
        'auto_generate_proxy_classes' => true,
        'naming_strategy' => 'doctrine.orm.naming_strategy.underscore_number_aware',
        'auto_mapping' => true,
    ],
]);
