<?php

declare(strict_types=1);

/*
 * This file is part of the package crell/planedo-bundle.
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Crell\Bundle\Planedo\Tests\Functional;

use Psr\Container\ContainerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

trait HasherWrapper
{
    private UserPasswordHasherInterface $hasher;

    protected function hasher(): UserPasswordHasherInterface
    {
        return $this->hasher ??= $this->getHasher();
    }

    protected function getHasher(): UserPasswordHasherInterface
    {
        /** @var UserPasswordHasherInterface $hasher */
        $hasher = self::getContainer()->get(UserPasswordHasherInterface::class);

        return $hasher;
    }

    abstract protected static function getContainer(): ContainerInterface;
}
