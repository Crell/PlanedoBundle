<?php

declare(strict_types=1);

/*
 * This file is part of the package crell/planedo-bundle.
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Crell\Bundle\Planedo\Tests\Functional\Controller;

use Crell\Bundle\Planedo\DataFixtures\FeedFixtures;
use Crell\Bundle\Planedo\Tests\EntityManagerWrapper;
use Crell\Bundle\Planedo\Tests\Functional\DataFixtures\FeedTestFixtures;
use Crell\Bundle\Planedo\Tests\Functional\MockClock;
use Crell\Bundle\Planedo\Tests\Functional\MockFeedReaderClient;
use Crell\Bundle\Planedo\Tests\Functional\SetupUtils;
use Crell\Bundle\Planedo\Tests\Functional\WebTestCase;

/**
 * @group public
 */
class HtmlFeedControllerTest extends WebTestCase
{
    use SetupUtils;
    use EntityManagerWrapper;
    use MockClock;
    use MockFeedReaderClient;

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
    public function mainFeedHasData(): void
    {
        $this->mockClock(new \DateTimeImmutable('2021-11-15'));
        $this->mockFeedClient();
        $this->populateFeeds();

        $crawler = $this->client->request('GET', '/');

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('content-type', 'text/html; charset=UTF-8');

        $container = self::getContainer();

        // Confirm the number of articles on the first page.
        $articles = $crawler->filter('article');
        self::assertCount($container->getParameter('planedo.itemsPerPage'), $articles);

        // Confirm there is next link but no prev link, since it's the front page.
        $next = $crawler->filter('a[rel="next"]');
        self::assertCount(1, $next);
        $prev = $crawler->filter('a[rel="prev"]');
        self::assertCount(0, $prev);

        // Only 11 items would have survived the old-data filter when adding.
        $this->assertRawEntryCount(11);
    }

    /**
     * @test
     */
    public function sidebarsDisplay(): void
    {
        $this->mockClock(new \DateTimeImmutable('2021-11-15'));
        $this->mockFeedClient();
        $this->populateFeeds();

        $crawler = $this->client->request('GET', '/');

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('content-type', 'text/html; charset=UTF-8');

        self::assertSelectorTextSame('div.sidebar aside.most-active > h2', 'Popular feeds');
        self::assertSelectorTextSame('div.sidebar aside.feed-links > h2', 'Feeds');

        $feeds = $crawler->filter('div.sidebar aside.most-active > ul > li ');
        self::assertCount(3, $feeds);
    }
}
