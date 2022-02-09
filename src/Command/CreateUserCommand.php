<?php

namespace Crell\Bundle\Planedo\Command;

use Crell\Bundle\Planedo\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'planedo:create-user',
    description: 'Add a new administrative user',
)]
class CreateUserCommand extends Command
{
    public function __construct(
        private UserPasswordHasherInterface $hasher,
        private EntityManagerInterface $em,
        private LoggerInterface $logger = new NullLogger(),
        string $name = null,
    ) {
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this
            ->addOption('email', 'em', InputOption::VALUE_OPTIONAL, 'Email address')
            ->addOption('password', 'p', InputOption::VALUE_OPTIONAL, 'Password')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $validator = static function (?string $answer): string {
            if (!is_string($answer) || strlen($answer) < 2) {
                throw new \RuntimeException('Your response must be at least two characters.');
            }
            return $answer;
        };

        $email = $input->getOption('email')
            ?? $io->ask('Email address for the new user', '', $validator);
        $password = $input->getOption('password')
            ?? $io->askHidden('Password for the new user', $validator);

        try {
            $user = User::create($email);

            // hash the password (based on the security.yaml config for the $user class)
            $hashedPassword = $this->hasher->hashPassword($user, $password);
            $user->setPassword($hashedPassword);
            $this->em->persist($user);
            $this->em->flush();

            $this->logger->info('New user created: {email}', [
                'email' => $email,
            ]);
            $io->success('User created.');
            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->logger->error('Failed creating user: {email}', [
                'email' => $email,
                'exception' => $e,
            ]);
            $io->error('Error creating user');

            return Command::FAILURE;
        }
    }
}
