<?php

namespace Crell\Bundle\Planedo\Command;

use Crell\Bundle\Planedo\Message\PurgeOldEntries;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsCommand(
    name: 'planedo:purge-old',
    description: 'Purge old feed entries. The age to purge is controlled by the app.feeds.purge-before parameter.',
)]
class PurgeOldCommand extends Command
{
    public function __construct(
        protected MessageBusInterface $bus,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $this->bus->dispatch(new PurgeOldEntries());

        $io->success('Old entries queued for deletion.');

        return Command::SUCCESS;
    }
}
