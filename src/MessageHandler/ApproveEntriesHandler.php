<?php

declare(strict_types=1);

/*
 * This file is part of the package crell/planedo-bundle.
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Crell\Bundle\Planedo\MessageHandler;

use Crell\Bundle\Planedo\Entity\FeedEntry;
use Crell\Bundle\Planedo\Message\ApproveEntries;
use Crell\Bundle\Planedo\Repository\FeedEntryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class ApproveEntriesHandler
{
    private FeedEntryRepository $entryRepo;

    public function __construct(
        private EntityManagerInterface $em,
    ) {
        $this->entryRepo = $this->em->getRepository(FeedEntry::class);
    }

    public function __invoke(ApproveEntries $message): void
    {
        $this->entryRepo->approve(...$message->entryIds);
    }
}
