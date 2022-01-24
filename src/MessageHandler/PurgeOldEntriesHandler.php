<?php

namespace Crell\Bundle\Planedo\MessageHandler;

use Crell\Bundle\Planedo\Entity\FeedEntry;
use Crell\Bundle\Planedo\Message\PurgeOldEntries;
use Crell\Bundle\Planedo\Repository\FeedEntryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Clock\ClockInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class PurgeOldEntriesHandler implements MessageHandlerInterface
{
    protected FeedEntryRepository $entryRepo;

    public function __construct(
        private EntityManagerInterface $em,
        private ClockInterface $clock,
        protected string $purgeBefore,
        private ?LoggerInterface $logger = new NullLogger(),
    ) {
        $this->entryRepo = $this->em->getRepository(FeedEntry::class);
    }

    public function __invoke(PurgeOldEntries $message)
    {
        $deleteBefore = $this->clock->now()->modify($this->purgeBefore);

        try {
            $this->entryRepo->deleteOlderThan($deleteBefore);
        } catch (\Exception $e) {
            $this->logger->error('Failed purging old entries: {message}', [
                'exception' => $e,
                'message' => $e->getMessage(),
            ]);
        }
    }
}
