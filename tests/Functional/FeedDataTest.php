<?php

declare(strict_types=1);

namespace Crell\Bundle\Planedo\Tests\Functional;

use Crell\Bundle\Planedo\DataFixtures\FeedFixtures;
use Crell\Bundle\Planedo\Entity\FeedEntry;
use Crell\Bundle\Planedo\Repository\FeedEntryRepository;
use Crell\Bundle\Planedo\Tests\Functional\DataFixtures\FeedTestFixtures;
use Crell\Bundle\Planedo\Tests\Functional\SetupUtils;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class FeedDataTest extends KernelTestCase
{
    use DatabasePrimer;
    use DatabaseFixtures;
    use MockClock;
    use MockFeedReaderClient;

    use SetupUtils;

    public function setUp(): void
    {
        parent::setUp();
        self::bootKernel();
        $this->prime();
        $this->addFixture(new FeedFixtures());
        $this->addFixture(new FeedTestFixtures());
        $this->executeFixtures();
    }

    /**
     * @test
     */
    public function rejectedEntriesDontShow(): void
    {
        $entryToExclude = 'https://www.example.com/blog/b';

        $this->mockClock(new \DateTimeImmutable('02 Dec 2021 01:01:01 +0000'));
        $this->mockFeedClient();
        $this->populateFeeds();

        $em = $this->entityManager();

        $entry = $this->feedEntryRepo()->find($entryToExclude);
        $entry->setApproved(false);
        $em->persist($entry);
        $em->flush();

        $entries = $this->feedEntryRepo()->latestEntriesPaginator(0);

        /** @var FeedEntry $entry */
        foreach ($entries as $entry) {
            self::assertNotEquals($entryToExclude, $entry->getId());
        }
    }

    /**
     * @test
     */
    public function inactiveFeedsDontShow(): void
    {
        $this->mockClock(new \DateTimeImmutable('02 Dec 2021 01:01:01 +0000'));
        $this->mockFeedClient();
        $this->populateFeeds();

        // Disable one feed, even though its data has been fetched.
        $em = $this->entityManager();
        $feed = $this->feedRepo()->findOneByFeedLink('https://www.garfieldtech.com/blog/feed');

        $feed->setActive(false);
        $em->persist($feed);
        $em->flush();

        $entries = $this->feedEntryRepo()->latestEntriesPaginator(0);

        /** @var FeedEntry $entry */
        foreach ($entries as $entry) {
            self::assertNotEquals($feed->getId(), $entry->getFeed()->getId());
        }
    }

}
