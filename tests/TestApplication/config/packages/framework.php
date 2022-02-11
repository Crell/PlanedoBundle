<?php

declare(strict_types=1);

/*
 * This file is part of the package crell/planedo-bundle.
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

$container->loadFromExtension('framework', [
    'secret' => 'F00',
    'csrf_protection' => true,
    'session' => [
        'handler_id' => null,
        'storage_factory_id' => 'session.storage.factory.mock_file',
    ],
    'test' => true,
]);
