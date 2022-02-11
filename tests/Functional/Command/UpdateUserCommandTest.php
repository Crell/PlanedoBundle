<?php

declare(strict_types=1);

/*
 * This file is part of the package crell/planedo-bundle.
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Crell\Bundle\Planedo\Tests\Functional\Command;

use Crell\Bundle\Planedo\Entity\User;
use Crell\Bundle\Planedo\Tests\Functional\DatabaseFixtures;
use Crell\Bundle\Planedo\Tests\Functional\DatabasePrimer;
use Crell\Bundle\Planedo\Tests\Functional\DataFixtures\UserFixtures;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * @group cli
 */
class UpdateUserCommandTest extends KernelTestCase
{
    use DatabasePrimer;
    use DatabaseFixtures;

    public function setUp(): void
    {
        parent::setUp();
        self::bootKernel();
        $this->prime();
        /** @var UserPasswordHasherInterface $hasher */
        $hasher = self::getContainer()->get(UserPasswordHasherInterface::class);
        $this->addFixture(new UserFixtures($hasher));
        $this->executeFixtures();
    }

    protected const Command = 'planedo:update-user';

    /**
     * @test
     */
    public function changeEmail(): void
    {
        $command = (new Application(static::$kernel))->find('planedo:update-user');
        $commandTester = new CommandTester($command);

        $exitCode = $commandTester->execute([
            'email' => 'me@me.com',
            '--email' => 'you@me.com',
        ]);
        $output = $commandTester->getDisplay();
        self::assertStringContainsString('User updated', $output);
        self::assertSame(Command::SUCCESS, $exitCode);

        $userRepository = self::getContainer()->get('doctrine')->getRepository(User::class);
        $foundUser = $userRepository->findOneByEmail('you@me.com');
        self::assertNotNull($foundUser);
    }

    /**
     * @test
     */
    public function changePassword(): void
    {
        $newPassword = 'qwer';

        $command = (new Application(static::$kernel))->find('planedo:update-user');
        $commandTester = new CommandTester($command);

        $commandTester->setInputs([$newPassword, $newPassword]);
        $exitCode = $commandTester->execute([
            'email' => 'me@me.com',
            '--password' => true,
        ]);
        $output = $commandTester->getDisplay();
        self::assertStringContainsString('User updated', $output);
        self::assertSame(Command::SUCCESS, $exitCode);

        $userRepository = self::getContainer()->get('doctrine')->getRepository(User::class);
        $foundUser = $userRepository->findOneByEmail('me@me.com');

        self::markTestIncomplete('Testing the password hash is not working yet.');
        $userPasswordHasher = self::getContainer()->get(UserPasswordHasherInterface::class);
        $expectedHash = $userPasswordHasher->hashPassword($foundUser, $newPassword);
        self::assertEquals($expectedHash, $foundUser->getPassword());
    }
}
