<?php

declare(strict_types=1);

namespace Crell\Bundle\Planedo\Tests;

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
    public function user_change_own_email(): void
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
            'user_settings[email]' => 'other@me.com'
        ]);

        $client->followRedirect();

        $user = $this->userRepo()->findOneByEmail('other@me.com');
        self::assertNotNull($user);
    }

    /**
     * @test
     */
    public function user_change_own_password(): void
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

        $this->markTestIncomplete('Testing the password hash is not working yet.');

        $expectedHash = $this->hasher()->hashPassword($foundUser, $newPassword);
        self::assertEquals($expectedHash, $foundUser->getPassword());
    }

    /**
     * @test
     */
    public function user_change_own_email_and_password(): void
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

        $this->markTestIncomplete('Testing the password hash is not working yet.');
        $expectedHash = $this->hasher()->hashPassword($foundUser, $newPassword);
        self::assertEquals($expectedHash, $foundUser->getPassword());

    }

}