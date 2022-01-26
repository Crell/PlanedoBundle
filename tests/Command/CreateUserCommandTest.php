<?php

declare(strict_types=1);

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
    protected const Command = 'app:create-user';

    use EntityManagerWrapper;
    use UserUtils;
    use CommandUtils;

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
        $this->assertStringContainsString('User created', $output);

        $foundUser = $this->userRepo()->findOneByEmail('me@me.com');

        self::assertNotNull($foundUser);
    }
}
