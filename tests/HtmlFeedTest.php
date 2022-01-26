<?php

namespace Crell\Bundle\Planedo\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * @group public
 */
class HtmlFeedTest extends WebTestCase
{
    use SetupUtils;
    use EntityManagerWrapper;

    /**
     * @test
     */
    public function main_feed_has_data(): void
    {
        $client = static::createClient();

        $this->mockClock(new \DateTimeImmutable('2021-11-15'));
        $this->mockFeedClient();
        $this->populateFeeds();

        $crawler = $client->request('GET', '/');

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('content-type', 'text/html; charset=UTF-8');

        $container = self::getContainer();

        // Confirm the number of articles on the first page.
        $articles = $crawler->filter('article');
        self::assertCount($container->getParameter('app.feeds.items-per-page'), $articles);

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
    public function sidebars_display(): void
    {
        $client = static::createClient();

        $this->mockClock(new \DateTimeImmutable('2021-11-15'));
        $this->mockFeedClient();
        $this->populateFeeds();

        $crawler = $client->request('GET', '/');

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('content-type', 'text/html; charset=UTF-8');

        $container = self::getContainer();

        self::assertSelectorTextSame('div.sidebar aside.most-active > h2', 'Popular feeds');
        self::assertSelectorTextSame('div.sidebar aside.feed-links > h2', 'Feeds');

        $feeds = $crawler->filter('div.sidebar aside.most-active > ul > li ');
        self::assertCount(3, $feeds);
    }

    /**
     * @test
     */
    public function rejected_entries_dont_show(): void
    {
        $entryToExclude = 'https://www.example.com/blog/b';

        $excludedContent = 'Description B';

        $client = static::createClient();

        $this->mockClock(new \DateTimeImmutable('02 Dec 2021 01:01:01 +0000'));
        $this->mockFeedClient();
        $this->populateFeeds();

        $em = $this->entityManager();

        $entry = $this->feedEntryRepo()->find($entryToExclude);
        $entry->setApproved(false);
        $em->persist($entry);
        $em->flush();

        $crawler = $client->request('GET', '/');
        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('content-type', 'text/html; charset=UTF-8');

        // Confirm that the rejected entry is not here.
        // @todo I'm pretty sure this is a stupid way of checking this.
        $response = $client->getResponse();
        self::assertStringNotContainsString($excludedContent, $response->getContent());
        $link = $crawler->filter(sprintf('a[href="%s"]', $entryToExclude));
        self::assertCount(0, $link);
    }

    /**
     * @test
     */
    public function inactive_feeds_dont_show(): void
    {
        $entryToExclude = 'https://www.example.com/blog/b';

        $excludedContent = 'Description B';

        $client = static::createClient();

        $this->mockClock(new \DateTimeImmutable('02 Dec 2021 01:01:01 +0000'));
        $this->mockFeedClient();
        $this->populateFeeds();


        // Disable one feed, even though its data has been fetched.

        $em = $this->entityManager();

        $feeds = $this->feedRepo()->findAll();
        foreach ($feeds as $f) {
            if ($f->getFeedLink() === 'https://www.garfieldtech.com/blog/feed') {
                $f->setActive(false);
                $em->persist($f);
            }
        }
        $em->flush();

        $crawler = $client->request('GET', '/');
        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('content-type', 'text/html; charset=UTF-8');

        // Confirm that the disabled feed doesn't show.
        // @todo I'm pretty sure this is a stupid way of checking this.
        $response = $client->getResponse();
        self::assertStringNotContainsString('garfieldtech', $response->getContent());
    }
}
