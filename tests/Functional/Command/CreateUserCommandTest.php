<?php

declare(strict_types=1);

/*
 * This file is part of the package crell/planedo-bundle.
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Crell\Bundle\Planedo\Tests\Functional\Command;

use Crell\Bundle\Planedo\Entity\User;
use Crell\Bundle\Planedo\Tests\Functional\DatabasePrimer;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @group cli
 */
class CreateUserCommandTest extends KernelTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        self::bootKernel();
        DatabasePrimer::prime(self::$kernel);
    }

    /**
     * @test
     */
    public function createUser(): void
    {
        $command = (new Application(static::$kernel))->find('planedo:create-user');
        $commandTester = new CommandTester($command);

        $exitCode = $commandTester->execute([
            '--email' => 'me@me.com',
            '--password' => 'asdf',
        ]);
        $output = $commandTester->getDisplay();
        self::assertStringContainsString('User created', $output);

        $userRepository = $this->getContainer()->get('doctrine')->getRepository(User::class);
        $foundUser = $userRepository->findOneByEmail('me@me.com');
        self::assertNotNull($foundUser);
        self::assertSame(Command::SUCCESS, $exitCode);
    }
}
