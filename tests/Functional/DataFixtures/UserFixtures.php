<?php

declare(strict_types=1);

/*
 * This file is part of the package crell/planedo-bundle.
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Crell\Bundle\Planedo\Tests\Functional\DataFixtures;

use Crell\Bundle\Planedo\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    public function __construct(
        protected UserPasswordHasherInterface $userPasswordHasher,
    ) {}

    public function load(ObjectManager $manager): void
    {
        $user = User::create('me@me.com');
        $user->setPassword($this->userPasswordHasher->hashPassword($user, 'secr0t'));
        $manager->persist($user);
        $manager->flush();
    }
}
