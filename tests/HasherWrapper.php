<?php

declare(strict_types=1);

namespace Crell\Bundle\Planedo\Tests;

use Psr\Container\ContainerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

trait HasherWrapper
{
    private ContainerInterface $container;

    private UserPasswordHasherInterface $hasher;

    protected function hasher(): UserPasswordHasherInterface
    {
        return $this->hasher ??= $this->getHasher();
    }

    protected function getHasher(): UserPasswordHasherInterface
    {
        /** @var UserPasswordHasherInterface $hasher */
        $hasher = $this->container->get(UserPasswordHasherInterface::class);
        return $hasher;
    }
}