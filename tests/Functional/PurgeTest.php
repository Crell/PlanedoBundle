<?php

declare(strict_types=1);

/*
 * This file is part of the package crell/planedo-bundle.
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Crell\Bundle\Planedo\Tests\Functional;

use Crell\Bundle\Planedo\DataFixtures\FeedFixtures;
use Crell\Bundle\Planedo\Message\PurgeOldEntries;
use Crell\Bundle\Planedo\Tests\Functional\DataFixtures\FeedTestFixtures;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * See the TestApplication/config/packages/test/planedo.php file for non-default configuration.
 */
class PurgeTest extends KernelTestCase
{
    use SetupUtils;
    use DatabasePrimer;
    use DatabaseFixtures;
    use MockClock;
    use MockFeedReaderClient;

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
    public function oldEntriesGetPurged(): void
    {
        // The first will allow through a few items.
        $clock = $this->mockClock(new \DateTimeImmutable('02 Dec 2021 01:01:01 +0000'));

        $this->mockFeedClient();
        $this->populateFeeds();

        $this->assertRawEntryCount(5);

        /** @var MessageBusInterface $bus */
        $bus = self::getContainer()->get(MessageBusInterface::class);

        // Fast forward time.
        $clock->set(new \DateTimeImmutable('31 Dec 2021 01:01:01 +0000'));

        // Now all items should be old enough to get purged, leaving none left.
        $bus->dispatch(new PurgeOldEntries());

        $this->assertRawEntryCount(0);
    }
}
