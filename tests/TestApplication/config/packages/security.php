<?php

declare(strict_types=1);

/*
 * This file is part of the package crell/planedo-bundle.
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

use Symfony\Component\Security\Core\User\InMemoryUser;

$container->loadFromExtension('security', [
    'enable_authenticator_manager' => true,
    'password_hashers' => [
        InMemoryUser::class => 'plaintext',
    ],
    'providers' => [
        'test_users' => [
            'memory' => [
                'users' => [
                    'admin' => [
                        'password' => '1234',
                        'roles' => ['ROLE_ADMIN'],
                    ],
                ],
            ],
        ],
    ],
    'firewalls' => [
        'secure_admin' => [
            'pattern' => '^/secure_admin',
            'provider' => 'test_users',
            'http_basic' => null,
            'logout' => null,
        ],
    ],
    'access_control' => [
        ['path' => '^/secure_admin', 'roles' => ['ROLE_ADMIN']],
    ],
]);
