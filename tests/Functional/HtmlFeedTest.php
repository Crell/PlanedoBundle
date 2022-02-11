<?php

declare(strict_types=1);

/*
 * This file is part of the package crell/planedo-bundle.
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Crell\Bundle\Planedo\Tests;

use Crell\Bundle\Planedo\Repository\FeedEntryRepository;
use Crell\Bundle\Planedo\Tests\Functional\MockClockTrait;
use Crell\Bundle\Planedo\Tests\Functional\MockFeedReaderClientTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * @group public
 */
class HtmlFeedTest extends WebTestCase
{
    use SetupUtils;
    use EntityManagerWrapper;
    use MockClockTrait;
    use MockFeedReaderClientTrait;

    /**
     * @test
     */
    public function mainFeedHasData(): void
    {
        $client = static::createClient();

        $this->mockClock(new \DateTimeImmutable('2021-11-15'));
        $this->mockFeedClient();
        $this->populateFeeds();

        $crawler = $client->request('GET', '/');

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('content-type', 'text/html; charset=UTF-8');

        $container = self::container();

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
        $client = static::createClient();

        $this->mockClock(new \DateTimeImmutable('2021-11-15'));
        $this->mockFeedClient();
        $this->populateFeeds();

        $crawler = $client->request('GET', '/');

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('content-type', 'text/html; charset=UTF-8');

        $container = self::container();

        self::assertSelectorTextSame('div.sidebar aside.most-active > h2', 'Popular feeds');
        self::assertSelectorTextSame('div.sidebar aside.feed-links > h2', 'Feeds');

        $feeds = $crawler->filter('div.sidebar aside.most-active > ul > li ');
        self::assertCount(3, $feeds);
    }

}
