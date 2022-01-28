<?php

declare(strict_types=1);

namespace Crell\Bundle\Planedo\Tests;

use Crell\Bundle\Planedo\Entity\Feed;
use Crell\Bundle\Planedo\Entity\FeedEntry;
use Crell\Bundle\Planedo\Message\UpdateFeed;
use Crell\Bundle\Planedo\Repository\FeedEntryRepository;
use Crell\Bundle\Planedo\Tests\Mocks\MockFeedReaderHttpClient;
use Crell\Bundle\Planedo\Tests\Mocks\SettableClock;
use Laminas\Feed\Reader\Http\ClientInterface;
use Psr\Clock\ClockInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

trait SetupUtils
{
    use EntityManagerWrapper;

    private ContainerInterface $container;

    protected function getContainer(): ContainerInterface
    {
        return $this->container;
    }

    protected function assertRawEntryCount(int $expected): void
    {
        $em = $this->entityManager();

        /** @var FeedEntryRepository $entryRepo */
        $entryRepo = $em->getRepository(FeedEntry::class);
        $entries = $entryRepo->findAll();
        self::assertCount($expected, $entries);
    }

    protected function mockFeedClient(): void
    {
        $container = self::getContainer();

        $mockClient = new MockFeedReaderHttpClient([
            'https://www.garfieldtech.com/blog/feed' => 'tests/feed-data/garfieldtech.rss',
            'http://www.planet-php.org/rss/' => 'tests/feed-data/planetphp.092.rss',
            'http://www.planet-php.org/rdf/' => 'tests/feed-data/planetphp.10.xml',
            'https://www.php.net/feed.atom' => 'tests/feed-data/phpnet.atom',
            'http://www.example.com/' => 'tests/feed-data/fake1.rss',
        ]);

        $container->set(ClientInterface::class, $mockClient);
    }

    protected function mockClock(\DateTimeImmutable $time): SettableClock
    {
        $container = $this->getContainer();
        $clock = new SettableClock($time);
        $container->set(ClockInterface::class, $clock);
        return $clock;
    }

    protected function populateFeeds(): void
    {
        $container = self::getContainer();

        /** @var MessageBusInterface $bus */
        $bus = $container->get(MessageBusInterface::class);

        $em = $this->entityManager();

        /** @var Feed[] $feeds */
        $feeds = $em->getRepository(Feed::class)->findAll();
        self::assertCount(3, $feeds);
        foreach ($feeds as $feed) {
            $bus->dispatch(new UpdateFeed($feed->getId()));
        }
    }
}
