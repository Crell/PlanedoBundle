<?php

declare(strict_types=1);

/*
 * This file is part of the package crell/planedo-bundle.
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Crell\Bundle\Planedo\Tests;

use Crell\Bundle\Planedo\Message\PurgeOldEntries;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Messenger\MessageBusInterface;

class PurgeTest extends TestCase
{
    use SetupUtils;

    protected static Kernel $kernel;

    /**
     * @test
     */
    public function oldEntriesGetPurged(): void
    {
        $container = $this->initialize(['purge_before' => '-10 days']);

        // The first will allow through a few items.
        $clock = $this->mockClock(new \DateTimeImmutable('02 Dec 2021 01:01:01 +0000'));

        $this->mockFeedClient();
        $this->populateFeeds();

        $this->assertRawEntryCount(5);

        /** @var MessageBusInterface $bus */
        $bus = $container->get(MessageBusInterface::class);

        // Fast forward time.
        $clock->set(new \DateTimeImmutable('31 Dec 2021 01:01:01 +0000'));

        // Now all items should be old enough to get purged, leaving none left.
        $bus->dispatch(new PurgeOldEntries());

        $this->assertRawEntryCount(0);
    }
}
