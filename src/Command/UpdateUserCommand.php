<?php

namespace Crell\Bundle\Planedo\Command;

use Crell\Bundle\Planedo\Entity\User;
use Crell\Bundle\Planedo\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'planedo:update-user',
    description: 'Updates an existing user',
)]
class UpdateUserCommand extends Command
{
    private readonly UserRepository $userRepo;

    public function __construct(
        private UserPasswordHasherInterface $hasher,
        private EntityManagerInterface $em,
        private LoggerInterface $logger = new NullLogger(),
    ) {
        parent::__construct();

        $this->userRepo = $em->getRepository(User::class);
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::OPTIONAL, 'Email of the user to update')
            ->addOption('email', 'em', InputOption::VALUE_OPTIONAL, 'New email address')
            ->addOption('password', 'p', InputOption::VALUE_NONE, 'Prompt for new password')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $email = $input->getArgument('email');

        $user = $this->userRepo->findOneByEmail($email);

        $newEmail = $input->getOption('email');

        $passwordChangeRequested = $input->getOption('password');

        if (!$newEmail && !$passwordChangeRequested) {
            $io->error('You must specify a new email address or prompt for a password change.');
            return Command::INVALID;
        }

        if (!$user) {
            $io->error(sprintf('No such user found: %s', $email));
            return Command::FAILURE;
        }

        $validator = static function (?string $answer): string {
            if (!is_string($answer) || strlen($answer) < 2) {
                throw new \RuntimeException('Your response must be at least two characters.');
            }
            return $answer;
        };

        if ($newEmail) {
            $newEmail = $validator($newEmail);
            $user->setEmail($newEmail);
            $this->logger->info('User updated: {email} (now {new_email})', [
                'email' => $email,
                'new_email' => $newEmail,
            ]);
        }

        if ($passwordChangeRequested) {
            $password = $io->askHidden('New password', $validator);
            $password2 = $io->askHidden('New password again', $validator);

            if ($password !== $password2) {
                $io->error('Passwords did not match');
                return Command::INVALID;
            }

            $hashedPassword = $this->hasher->hashPassword($user, $password);
            $user->setPassword($hashedPassword);
        }

        $this->em->persist($user);
        $this->em->flush();

        if ($newEmail) {
            $this->logger->info('User updated: {email} (now {new_email})', [
                'email' => $email,
                'new_email' => $newEmail,
            ]);
        }
        if ($passwordChangeRequested) {
            $this->logger->info('Password changed for user: {email}', [
                'email' => $newEmail ?? $email,
            ]);
        }

        $io->success('User updated.');

        return Command::SUCCESS;
    }
}
