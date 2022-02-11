<?php

declare(strict_types=1);

/*
 * This file is part of the package crell/planedo-bundle.
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Crell\Bundle\Planedo\Tests\Functional\Controller\Admin;

use Crell\Bundle\Planedo\DataFixtures\FeedFixtures;
use Crell\Bundle\Planedo\Tests\EntityManagerWrapper;
use Crell\Bundle\Planedo\Tests\Functional\DataFixtures\FeedTestFixtures;
use Crell\Bundle\Planedo\Tests\Functional\SetupUtils;
use Crell\Bundle\Planedo\Tests\Functional\WebTestCase;
use Crell\Bundle\Planedo\Tests\UserUtils;

/**
 * @group admin
 */
class AdminTest extends WebTestCase
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
    public function anonUserGetsLoginFormOnAdmin(): void
    {
        // Don't login first!

        $this->client->request('GET', '/admin');
        $crawler = $this->client->followRedirect();

        self::assertResponseIsSuccessful();

        $form = $crawler->filter('form[action=""]');

        self::assertEquals('Planedo login', $crawler->filter('title')->text());

        self::assertCount(1, $form);
    }

    /**
     * @test
     */
    public function feedIndexLoads(): void
    {
        $this->mockClock(new \DateTimeImmutable('02 Dec 2021 01:01:01 +0000'));
        $this->mockFeedClient();
        $this->populateFeeds();

        // It is not at all clear why we need to send a dummy request
        // before logging in.  If we don't, the loginUser() call fails with
        // a message that we cannot get a container from a non-booted kernel,
        // yet the kernel is definitely booted by this point.
        $this->client->request('GET', '/admin');

        // Login.
        $adminUser = $this->createUser('me@me.com', 'asdf');
        $this->client->loginUser($adminUser);

        $this->client->request('GET', '/admin');
        $this->client->followRedirect();

        $crawler = $this->client->clickLink('Feeds');

        self::assertResponseIsSuccessful();
        self::assertEquals('Feeds', $crawler->filter('title')->text());
    }

    /**
     * @test
     */
    public function feedEntryIndexLoads(): void
    {
        $this->mockClock(new \DateTimeImmutable('02 Dec 2021 01:01:01 +0000'));
        $this->mockFeedClient();
        $this->populateFeeds();

        // It is not at all clear why we need to send a dummy request
        // before logging in.  If we don't, the loginUser() call fails with
        // a message that we cannot get a container from a non-booted kernel,
        // yet the kernel is definitely booted by this point.
        $this->client->request('GET', '/admin');

        // Login.
        $adminUser = $this->createUser('me@me.com', 'asdf');
        $this->client->loginUser($adminUser);

        $this->client->request('GET', '/admin');
        $this->client->followRedirect();

        $crawler = $this->client->clickLink('Feed Entries');

        self::assertResponseIsSuccessful();
        self::assertEquals('Feed entries', $crawler->filter('title')->text());
    }
}
