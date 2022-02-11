<?php

declare(strict_types=1);

/*
 * This file is part of the package crell/planedo-bundle.
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Crell\Bundle\Planedo\Tests\Command;

use Crell\Bundle\Planedo\Tests\EntityManagerWrapper;
use Crell\Bundle\Planedo\Tests\UserUtils;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @group cli
 */
class CreateUserCommandTest extends KernelTestCase
{
    use EntityManagerWrapper;
    use UserUtils;
    use CommandUtils;
    protected const Command = 'planedo:create-user';

    /**
     * @test
     */
    public function create(): void
    {
        $kernel = self::bootKernel();
        $application = new Application($kernel);

        $tester = $this->executeCommand($application,
            args: [
                '--email' => 'me@me.com',
                '--password' => 'asdf',
            ],
        );

        $output = $tester->getDisplay();
        self::assertStringContainsString('User created', $output);

        $foundUser = $this->userRepo()->findOneByEmail('me@me.com');

        self::assertNotNull($foundUser);
    }
}
