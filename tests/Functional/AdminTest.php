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
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @group admin
 */
class AdminTest extends WebTestCase
{
    use UserUtils;
    use SetupUtils;

    protected static function container(): ContainerInterface
    {
        // TODO: Implement container() method.
    }

    /**
     * @test
     */
    public function anonUserGetsLoginFormOnAdmin(): void
    {
        $client = static::createClient();

        // Don't login.

        $client->request('GET', '/admin');
        $crawler = $client->followRedirect();

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
        $client = static::createClient();

        $this->mockClock(new \DateTimeImmutable('02 Dec 2021 01:01:01 +0000'));
        $this->mockFeedClient();
        $this->populateFeeds();

        // Login.
        $adminUser = $this->createUser('me@me.com', 'asdf');
        $client->loginUser($adminUser);

        $client->request('GET', '/admin');
        $client->followRedirect();

        $crawler = $client->clickLink('Feeds');

        self::assertResponseIsSuccessful();
        self::assertEquals('Feeds', $crawler->filter('title')->text());
    }

    /**
     * @test
     */
    public function feedEntryIndexLoads(): void
    {
        $client = static::createClient();

        $this->mockClock(new \DateTimeImmutable('02 Dec 2021 01:01:01 +0000'));
        $this->mockFeedClient();
        $this->populateFeeds();

        // Login.
        $adminUser = $this->createUser('me@me.com', 'asdf');
        $client->loginUser($adminUser);

        $client->request('GET', '/admin');
        $client->followRedirect();

        $crawler = $client->clickLink('Feed Entries');

        self::assertResponseIsSuccessful();
        self::assertEquals('Feed entries', $crawler->filter('title')->text());
    }
}
