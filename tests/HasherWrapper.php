<?php

declare(strict_types=1);

namespace Crell\Bundle\Planedo\Tests;

use Symfony\Component\DependencyInjection\ContainerInterface;
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
        $container = self::getContainer();
        /** @var UserPasswordHasherInterface $hasher */
        $hasher = $container->get(UserPasswordHasherInterface::class);
        return $hasher;
    }

    abstract protected static function getContainer(): ContainerInterface;
}