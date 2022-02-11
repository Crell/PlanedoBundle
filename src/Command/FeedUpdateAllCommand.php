<?php

declare(strict_types=1);

/*
 * This file is part of the package crell/planedo-bundle.
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Crell\Bundle\Planedo\Command;

use Crell\Bundle\Planedo\Message\UpdateFeed;
use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsCommand(
    name: 'planedo:feed:update-all',
    description: 'Add a short description for your command',
)]
class FeedUpdateAllCommand extends Command
{
    public function __construct(
        protected MessageBusInterface $bus,
        protected Connection $conn,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $result = $this->conn->executeQuery('SELECT id from feed');

        $count = 0;
        while ($record = $result->fetchAssociative()) {
            $this->bus->dispatch(new UpdateFeed($record['id']));
            ++$count;
        }

        $io->success(sprintf('%d feeds queued for updating.', $count));

        return Command::SUCCESS;
    }
}
