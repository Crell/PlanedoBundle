<?php

declare(strict_types=1);

/*
 * This file is part of the package crell/planedo-bundle.
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

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
