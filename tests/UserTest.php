<?php

declare(strict_types=1);

/*
 * This file is part of the package crell/planedo-bundle.
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Crell\Bundle\Planedo\Tests;

use Crell\Bundle\Planedo\Tests\Functional\SetupUtils;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * @group admin
 */
class UserTest extends WebTestCase
{
    use SetupUtils;
    use UserUtils;
    use EntityManagerWrapper;

    /**
     * @test
     */
    public function userChangeOwnEmail(): void
    {
        $client = static::createClient();

        // Login.
        $adminUser = $this->createUser('me@me.com', 'asdf');
        $client->loginUser($adminUser);

        $client->request('GET', '/admin');
        $client->followRedirect();

        $client->clickLink('Profile');
        self::assertResponseIsSuccessful();

        $client->submitForm('Save', [
            'user_settings[email]' => 'other@me.com',
        ]);

        $client->followRedirect();

        $user = $this->userRepo()->findOneByEmail('other@me.com');
        self::assertNotNull($user);
    }

    /**
     * @test
     */
    public function userChangeOwnPassword(): void
    {
        $newPassword = 'qwerty';

        $client = static::createClient();

        // Login.
        $adminUser = $this->createUser('me@me.com', 'asdf');
        $client->loginUser($adminUser);

        $client->request('GET', '/admin');
        $client->followRedirect();

        $client->clickLink('Profile');
        self::assertResponseIsSuccessful();

        $client->submitForm('Save', [
            'user_settings[plainPassword][first]' => $newPassword,
            'user_settings[plainPassword][second]' => $newPassword,
        ]);

        $client->followRedirect();

        $foundUser = $this->userRepo()->findOneByEmail('me@me.com');

        self::markTestIncomplete('Testing the password hash is not working yet.');

        $expectedHash = $this->hasher()->hashPassword($foundUser, $newPassword);
        self::assertEquals($expectedHash, $foundUser->getPassword());
    }

    /**
     * @test
     */
    public function userChangeOwnEmailAndPassword(): void
    {
        $newPassword = 'qwerty';

        $client = static::createClient();

        // Login.
        $adminUser = $this->createUser('me@me.com', 'asdf');
        $client->loginUser($adminUser);

        $client->request('GET', '/admin');
        $client->followRedirect();

        $client->clickLink('Profile');
        self::assertResponseIsSuccessful();

        $client->submitForm('Save', [
            'user_settings[email]' => 'other@me.com',
            'user_settings[plainPassword][first]' => $newPassword,
            'user_settings[plainPassword][second]' => $newPassword,
        ]);

        $client->followRedirect();

        $foundUser = $this->userRepo()->findOneByEmail('other@me.com');
        self::assertNotNull($foundUser);

        self::markTestIncomplete('Testing the password hash is not working yet.');
        $expectedHash = $this->hasher()->hashPassword($foundUser, $newPassword);
        self::assertEquals($expectedHash, $foundUser->getPassword());
    }
}
