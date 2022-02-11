<?php

declare(strict_types=1);

/*
 * This file is part of the package crell/planedo-bundle.
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Crell\Bundle\Planedo\Tests\Functional;

use Crell\Bundle\Planedo\DataFixtures\FeedFixtures;
use Crell\Bundle\Planedo\Tests\EntityManagerWrapper;
use Crell\Bundle\Planedo\Tests\Functional\DataFixtures\FeedTestFixtures;

/**
 * @group admin
 */
class UserTest extends WebTestCase
{
    use UserUtils;
    use EntityManagerWrapper;
    use SetupUtils;

    public function setUp(): void
    {
        parent::setUp();
        $this->addFixture(new FeedFixtures());
        $this->addFixture(new FeedTestFixtures());
        $this->executeFixtures();
    }

    /**
     * @test
     */
    public function userChangeOwnEmail(): void
    {
        // It is not at all clear why we need to send a dummy request
        // before logging in.  If we don't, the loginUser() call fails with
        // a message that we cannot get a container from a non-booted kernel,
        // yet the kernel is definitely booted by this point.
        $this->client->request('GET', '/');

        // Login.
        $adminUser = $this->createUser('me@me.com', 'asdf');
        $this->client->loginUser($adminUser);

        $this->client->request('GET', '/admin');
        $this->client->followRedirect();

        $this->client->clickLink('Profile');
        self::assertResponseIsSuccessful();

        $this->client->submitForm('Save', [
            'user_settings[email]' => 'other@me.com',
        ]);
        $this->client->followRedirect();

        $user = $this->userRepo()->findOneByEmail('other@me.com');
        self::assertNotNull($user);
    }

    /**
     * @test
     */
    public function userChangeOwnPassword(): void
    {
        // It is not at all clear why we need to send a dummy request
        // before logging in.  If we don't, the loginUser() call fails with
        // a message that we cannot get a container from a non-booted kernel,
        // yet the kernel is definitely booted by this point.
        $this->client->request('GET', '/');

        $newPassword = 'qwerty';

        // Login.
        $adminUser = $this->createUser('me@me.com', 'asdf');
        $this->client->loginUser($adminUser);

        $this->client->request('GET', '/admin');
        $this->client->followRedirect();

        $this->client->clickLink('Profile');
        self::assertResponseIsSuccessful();

        $this->client->submitForm('Save', [
            'user_settings[plainPassword][first]' => $newPassword,
            'user_settings[plainPassword][second]' => $newPassword,
        ]);

        $this->client->followRedirect();

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
        // It is not at all clear why we need to send a dummy request
        // before logging in.  If we don't, the loginUser() call fails with
        // a message that we cannot get a container from a non-booted kernel,
        // yet the kernel is definitely booted by this point.
        $this->client->request('GET', '/');

        $newPassword = 'qwerty';

        // Login.
        $adminUser = $this->createUser('me@me.com', 'asdf');
        $this->client->loginUser($adminUser);

        $this->client->request('GET', '/admin');
        $this->client->followRedirect();

        $this->client->clickLink('Profile');
        self::assertResponseIsSuccessful();

        $this->client->submitForm('Save', [
            'user_settings[email]' => 'other@me.com',
            'user_settings[plainPassword][first]' => $newPassword,
            'user_settings[plainPassword][second]' => $newPassword,
        ]);

        $this->client->followRedirect();

        $foundUser = $this->userRepo()->findOneByEmail('other@me.com');
        self::assertNotNull($foundUser);

        self::markTestIncomplete('Testing the password hash is not working yet.');
        $expectedHash = $this->hasher()->hashPassword($foundUser, $newPassword);
        self::assertEquals($expectedHash, $foundUser->getPassword());
    }
}
