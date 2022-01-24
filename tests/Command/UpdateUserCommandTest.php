<?php

declare(strict_types=1);

namespace Crell\Bundle\Planedo\Tests\Command;

use Crell\Bundle\Planedo\Tests\Command\CommandUtils;
use Crell\Bundle\Planedo\Tests\EntityManagerWrapper;
use Crell\Bundle\Planedo\Tests\HasherWrapper;
use Crell\Bundle\Planedo\Tests\UserUtils;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @group cli
 */
class UpdateUserCommandTest extends KernelTestCase
{
    protected const Command = 'app:update-user';

    use EntityManagerWrapper;
    use UserUtils;
    use HasherWrapper;
    use CommandUtils;

    /**
     * @test
     */
    public function change_email(): void
    {
        $kernel = self::bootKernel();
        $application = new Application($kernel);

        $this->createUser('me@me.com', 'asdf');

        $tester = $this->executeCommand($application, [
            // pass arguments to the helper
            'email' => 'me@me.com',
            '--email' => 'you@me.com',
        ]);

        // The output of the command in the console.
        $output = $tester->getDisplay();
        $this->assertStringContainsString('User updated', $output);

        $foundUser = $this->userRepo()->findOneByEmail('you@me.com');

        self::assertEquals('you@me.com', $foundUser->getEmail());
    }

    /**
     * @test
     */
    public function change_password(): void
    {
        $newPassword = 'qwer';

        $kernel = self::bootKernel();
        $application = new Application($kernel);

        $this->createUser('me@me.com', 'asdf');

        $tester = $this->executeCommand($application,
            args: [
                'email' => 'me@me.com',
                '--password' => true,
            ],
            inputs: [$newPassword, $newPassword],
        );

        // The output of the command in the console.
        $output = $tester->getDisplay();
        $this->assertStringContainsString('User updated', $output);

        $foundUser = $this->userRepo()->findOneByEmail('me@me.com');

        $this->markTestIncomplete('Testing the password hash is not working yet.');

        $expectedHash = $this->hasher()->hashPassword($foundUser, $newPassword);

        self::assertEquals($expectedHash, $foundUser->getPassword());
    }

}
