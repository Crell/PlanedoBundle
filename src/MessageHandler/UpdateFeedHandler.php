<?php

namespace Crell\Bundle\Planedo\MessageHandler;

use Crell\Bundle\Planedo\Entity\Feed;
use Crell\Bundle\Planedo\Entity\FeedEntry;
use Crell\Bundle\Planedo\FeedReader;
use Crell\Bundle\Planedo\Message\UpdateFeed;
use Crell\Bundle\Planedo\Repository\FeedEntryRepository;
use Crell\Bundle\Planedo\Repository\FeedRepository;
use Doctrine\ORM\EntityManagerInterface;
use Laminas\Feed\Reader\Collection\Author;
use Laminas\Feed\Reader\Entry\EntryInterface;
use Laminas\Feed\Reader\Feed\FeedInterface;
use Psr\Clock\ClockInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class UpdateFeedHandler implements MessageHandlerInterface
{
    private FeedRepository $feedRepo;
    private FeedEntryRepository $entryRepo;

    public function __construct(
        private EntityManagerInterface $em,
        private FeedReader $reader,
        private ClockInterface $clock,
        private string $purgeBefore,
        private LoggerInterface $logger = new NullLogger(),
    ) {
        $this->feedRepo = $this->em->getRepository(Feed::class);
        $this->entryRepo = $this->em->getRepository(FeedEntry::class);
    }

    public function __invoke(UpdateFeed $message): void
    {
        $feed = $this->feedRepo->find($message->feedId);

        if (is_null($feed)) {
            $this->logger->warning('Tried to fetch feed for {id}, but no feed was found.', ['id' => $message->feedId]);
            return;
        }

        // Do not update feeds that are not active/enabled.
        if (!$feed->isActive()) {
            return;
        }

        try {
            $feedData = $this->reader->import($feed?->getFeedLink());
        } catch (\Laminas\Feed\Reader\Exception\RuntimeException $e) {
            $this->logger->error('Exception caught importing feed {name}', [
                'name' => $feed->getTitle(),
                'exception' => $e,
            ]);
            return;
        }

        $this->em->wrapInTransaction(function (EntityManagerInterface $em) use ($feed, $feedData) {
            $feed = $this->updateFeed($feed, $feedData);

            $purgeBefore = $this->clock->now()->modify($this->purgeBefore);

            /** @var EntryInterface $item */
            foreach ($feedData as $item) {
                // @todo Turn this into a map/filter operation, once we depend on Crell/fp.
                if ($item->getDateCreated() >= $purgeBefore) {
                    $this->updateFeedEntry($item, $em, $feed);
                }
            }

            // Mark that it has been updated.
            $feed->setLastUpdated(new \DateTimeImmutable(timezone: new \DateTimeZone('UTC')));

            $em->persist($feed);
            $em->flush();
        });
    }

    protected function updateFeedEntry(EntryInterface $item, EntityManagerInterface $em, Feed $feed): FeedEntry
    {
        $entry = $em->find(FeedEntry::class, $item->getLink()) ?? new FeedEntry();

        /** @var Author $authors */
        $authors = $item->getAuthors() ?? [];
        $authorNames = [];
        foreach ($authors as $a) {
            $authorNames[] = $a['name'];
        }
        $entry->setAuthors($authorNames);

        $entry
            ->setFeed($feed)
            ->setTitle($item->getTitle())
            ->setLink($item->getLink())
            ->setDescription($item->getDescription() ?? '')
            ->setDateModified($item->getDateModified())
            ->setDateCreated($item->getDateCreated())
            ->setAuthors($authorNames)
        ;
        $em->persist($entry);

        return $entry;
    }

    protected function updateFeed(Feed $feed, FeedInterface $feedData): Feed
    {
        $optional = [
            'Link',
            // getFeedLink() is broken and buggy, so don't use it
            // cf: https://github.com/laminas/laminas-feed/issues/44
            //'FeedLink',
            'Copyright',
            'DateCreated',
            'DateModified',
            'Generator',
            'Language',
        ];

        // Update the feed itself with data from the feed.
        foreach ($optional as $method) {
            $val = $feedData->{'get' . $method}();
            if ($val) {
                $feed->{'set' . $method}($val);
            }
        }

        // Authors are an over-engineered array, which has a singular name
        // even though it's an iterable, even though getAuthors() is typed
        // to return an array. Bad design in Laminas Feed. That's why we can't
        // easily just use a map operation.
        /** @var Author $authors */
        $authors = $feedData->getAuthors() ?? [];
        $authorNames = [];
        foreach ($authors as $a) {
            $authorNames[] = $a['name'];
        }
        $feed->setAuthors($authorNames);

        return $feed;
    }
}
