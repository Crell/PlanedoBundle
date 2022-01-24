<?php

declare(strict_types=1);

namespace Crell\Bundle\Planedo\Tests;

use Crell\Bundle\Planedo\Entity\FeedEntry;
use Crell\Bundle\Planedo\Message\PurgeOldEntries;
use Crell\Bundle\Planedo\Repository\FeedEntryRepository;
use Crell\Bundle\Planedo\Tests\Mocks\SettableClock;
use Crell\Bundle\Planedo\Tests\SetupUtils;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Clock\ClockInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Messenger\MessageBusInterface;

class PurgeTest extends KernelTestCase
{
    use SetupUtils;

    /**
     * @test
     */
    public function old_entries_get_purged(): void
    {
        self::bootKernel();

        $container = self::getContainer();

        // The first will allow through a few items.
        $clock = $this->mockClock(new \DateTimeImmutable('02 Dec 2021 01:01:01 +0000'));

        $this->mockFeedClient();
        $this->populateFeeds();

        $this->assertRawEntryCount(5);

        $container = self::getContainer();

        /** @var MessageBusInterface $bus */
        $bus = $container->get(MessageBusInterface::class);

        // Fast forward time.
        $clock->set(new \DateTimeImmutable('31 Dec 2021 01:01:01 +0000'));

        // Now all items should be old enough to get purged, leaving none left.
        $bus->dispatch(new PurgeOldEntries());

        $this->assertRawEntryCount(0);
    }
}
