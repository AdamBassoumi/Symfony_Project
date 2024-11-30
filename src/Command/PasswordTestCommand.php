<?php

namespace App\Command;

use App\Entity\Utilisateur;
use App\Repository\UtilisateurRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class PasswordTestCommand extends Command
{
    private $userRepository;
    private $passwordHasher;

    public function __construct(UtilisateurRepository $userRepository, UserPasswordHasherInterface $passwordHasher)
    {
        parent::__construct();

        $this->userRepository = $userRepository;
        $this->passwordHasher = $passwordHasher;
    }

    // Use configure() method to set the command name
    protected function configure(): void
    {
        $this
            ->setName('app:test-password') // Set the name directly in configure() method
            ->setDescription('Test if a user password is correct');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $userId = 18; // Set the ID you want to test
        $utilisateur = $this->userRepository->find($userId);

        if (!$utilisateur) {
            $output->writeln('User not found.');
            return Command::FAILURE;
        }

        // The password to test
        $passwordToTest = 'dom';  // The password you're testing

        // Check if the password is valid
        if ($this->passwordHasher->isPasswordValid($utilisateur, $passwordToTest)) {
            $output->writeln('Password is valid!');
        } else {
            $output->writeln('Password is invalid.');
        }

        return Command::SUCCESS;
    }
}
