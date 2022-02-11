<?php

declare(strict_types=1);

/*
 * This file is part of the package crell/planedo-bundle.
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

use Symfony\Component\Security\Core\User\InMemoryUser;
use Crell\Bundle\Planedo\Entity\User;

$container->loadFromExtension('security', [
    'enable_authenticator_manager' => true,
    'password_hashers' => [
        InMemoryUser::class => 'plaintext',
    ],
    'providers' => [
        'app_user_provider' => [
            'entity' => [
                'class' => User::class,
                'property' => 'email',
            ],
        ],
    ],
    'firewalls' => [
        'main' => [
            'lazy' => true,
            'provider' => 'app_user_provider',
            'form_login' => [
                'login_path' => 'planedo_login',
                'check_path' => 'planedo_login',
                'enable_csrf' => false,
            ],
            'logout' => [
                'path' => 'planedo_logout',
            ],
        ],
    ],
    'access_control' => [
        ['path' => '^/admin', 'roles' => ['ROLE_ADMIN']],
    ],
]);
