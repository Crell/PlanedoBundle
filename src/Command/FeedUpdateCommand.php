<?php

namespace Crell\Bundle\Planedo\Command;

use Crell\Bundle\Planedo\Entity\Feed;
use Crell\Bundle\Planedo\Message\UpdateFeed;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsCommand(
    name: 'planedo:feed:update',
    description: 'Update a feed',
)]
class FeedUpdateCommand extends Command
{
    public function __construct(
        protected MessageBusInterface $bus,
        protected EntityManagerInterface $em,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('feedId', InputArgument::REQUIRED, 'The numeric ID of the feed to update.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $feedId = $input->getArgument('feedId');

        // Verify that the Feed exists. We don't actually need
        // the object itself, though.
        if (!$this->em->getRepository(Feed::class)->find($feedId)) {
            $io->error(sprintf('No feed found with id %d.', $feedId));
            return Command::INVALID;
        }

        if ($feedId) {
            $this->bus->dispatch(new UpdateFeed($feedId));
            $io->success('Feed queued for updating.');
            return Command::SUCCESS;
        }

        $io->error('You must specify a feed id.');
        return Command::INVALID;
    }
}
