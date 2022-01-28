<?php

declare(strict_types=1);

namespace Crell\Bundle\Planedo\Tests;

use Crell\Bundle\Planedo\Message\PurgeOldEntries;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Messenger\MessageBusInterface;

class PurgeTest extends TestCase
{
    use SetupUtils;

    protected static Kernel $kernel;

//    protected static function getContainer(): ContainerInterface
//    {
//        return self::$kernel->getContainer();
//    }

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

//        self::$kernel = new PlanedoTestingKernel();
//        self::$kernel->boot();
        //$container = self::$kernel->getContainer();
    }


    /**
     * @test
     */
    public function old_entries_get_purged(): void
    {
        $kernel = new PlanedoTestingKernel(['purge_before' => '-10 days']);
        $kernel->boot();
        $container = $this->container = $kernel->getContainer();

//        $container = self::getContainer();

        // The first will allow through a few items.
        $clock = $this->mockClock(new \DateTimeImmutable('02 Dec 2021 01:01:01 +0000'));

        $this->mockFeedClient();
        $this->populateFeeds();

        $this->assertRawEntryCount(5);

//        $container = self::getContainer();

        /** @var MessageBusInterface $bus */
        $bus = $container->get(MessageBusInterface::class);

        // Fast forward time.
        $clock->set(new \DateTimeImmutable('31 Dec 2021 01:01:01 +0000'));

        // Now all items should be old enough to get purged, leaving none left.
        $bus->dispatch(new PurgeOldEntries());

        $this->assertRawEntryCount(0);
    }
}
