<?php

declare(strict_types=1);

namespace Crell\Bundle\Planedo\Tests;

use Crell\Bundle\Planedo\Entity\User;

trait UserUtils
{
    use HasherWrapper;

    protected function createUser(string $email, string $password): User
    {
        $em = $this->entityManager();

        $user = User::create($email);

        $user->setPassword($this->hasher()->hashPassword($user, $password));
        $em->persist($user);
        $em->flush();

        return $user;
    }
}