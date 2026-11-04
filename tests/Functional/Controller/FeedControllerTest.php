<?php

declare(strict_types=1);

/*
 * This file is part of the package crell/planedo-bundle.
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Crell\Bundle\Planedo\Tests\Functional\Controller;

use Crell\Bundle\Planedo\DataFixtures\FeedFixtures;
use Crell\Bundle\Planedo\Entity\FeedEntry;
use Crell\Bundle\Planedo\Tests\Functional\DataFixtures\FeedTestFixtures;
use Crell\Bundle\Planedo\Tests\Functional\EntityManagerWrapper;
use Crell\Bundle\Planedo\Tests\Functional\SetupUtils;
use Crell\Bundle\Planedo\Tests\Functional\WebTestCase;
use Laminas\Feed\Reader\Reader;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[Group('public')]
class FeedControllerTest extends WebTestCase
{
    use EntityManagerWrapper;
    use SetupUtils;

    protected function setUp(): void
    {
        parent::setUp();
        $this->addFixture(new FeedFixtures());
        $this->addFixture(new FeedTestFixtures());
        $this->executeFixtures();
    }

    public static function feedTypeProvider(): iterable
    {
        yield 'Atom' => [
            'path' => '/atom',
            'contentType' => 'application/atom+xml',
        ];
        yield 'Rss' => [
            'path' => '/rss',
            'contentType' => 'application/rss+xml',
        ];
    }

    #[Test]
    #[DataProvider('feedTypeProvider')]
    public function feedHasData(string $path, string $contentType): void
    {
        $this->mockClock(new \DateTimeImmutable('2021-11-15'));
        $this->mockFeedClient();

        $this->populateFeeds();

        $this->client->request('GET', $path);
        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('content-type', $contentType);

        $feed = Reader::importString($this->client->getResponse()->getContent());

        // Confirm the number of articles in the first page of the feed.
        self::assertCount(self::getContainer()->getParameter('planedo.itemsPerPage'), $feed);

        // Only 11 items would have survived the old-data filter when adding.
        $this->assertRawEntryCount(11);
    }

    #[Test]
    #[DataProvider('feedTypeProvider')]
    public function rejectedEntriesDontShow(string $path, string $contentType): void
    {
        $entryToExclude = 'https://www.example.com/blog/b';

        $this->mockClock(new \DateTimeImmutable('02 Dec 2021 01:01:01 +0000'));
        $this->mockFeedClient();

        $this->populateFeeds();

        $em = $this->entityManager();

        /** @var FeedEntry $entry */
        $entry = $this->feedEntryRepo()->find($entryToExclude);
        $entry->setApproved(false);
        $em->persist($entry);
        $em->flush();

        $crawler = $this->client->request('GET', $path);
        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('content-type', $contentType);

        // Confirm the rejected entry is not in the feed.
        $feed = Reader::importString($this->client->getResponse()->getContent());
        foreach ($feed as $entry) {
            self::assertNotEquals($entryToExclude, $entry->getId(), 'Rejected entry found in feed.');
        }
    }

    #[Test]
    #[DataProvider('feedTypeProvider')]
    public function inactiveFeedsDontShow(string $path, string $contentType): void
    {
        $this->mockClock(new \DateTimeImmutable('02 Dec 2021 01:01:01 +0000'));
        $this->mockFeedClient();

        $this->populateFeeds();

        // Disable one feed, even though its data has been fetched.
        $em = $this->entityManager();

        $feeds = $this->feedRepo()->findAll();
        foreach ($feeds as $f) {
            if ('https://www.garfieldtech.com/blog/feed' === $f->getFeedLink()) {
                $f->setActive(false);
                $em->persist($f);
            }
        }
        $em->flush();

        $crawler = $this->client->request('GET', $path);
        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('content-type', $contentType);

        // Confirm that the disabled feed doesn't show.
        $feed = Reader::importString($this->client->getResponse()->getContent());
        foreach ($feed as $entry) {
            self::assertNotEquals('garfieldtech', $entry->getPermalink());
        }
    }
}
