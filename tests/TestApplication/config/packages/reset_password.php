<?php

declare(strict_types=1);

/*
 * This file is part of the package crell/planedo-bundle.
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

$container->loadFromExtension('symfonycasts_reset_password', [
    'request_password_repository' => \Crell\Bundle\Planedo\Repository\ResetPasswordRequestRepository::class,
]);
