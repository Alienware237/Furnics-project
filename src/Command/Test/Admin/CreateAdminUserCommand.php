<?php

namespace okpt\furnics\project\Command\Test\Admin;

use okpt\furnics\project\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-admin-user',
    description: 'Command to create new admin user',
)]
class CreateAdminUserCommand extends Command
{

    private $entityManager;
    private $passwordEncoder;

    public function __construct(EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordEncoder)
    {
        parent::__construct();

        $this->entityManager = $entityManager;
        $this->passwordEncoder = $passwordEncoder;
    }

    protected function configure()
    {
        $this->setDescription('Creates a new admin user.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // Create a new User entity
        $user = new User();
        $user->setFirstName('Leticia');
        $user->setLastName('Bouguem');
        $user->setEmail('admin@example.com');
        $user->setSalutation('Women');
        $user->setPassword($this->passwordEncoder->hashPassword($user, 'adminpassword'));
        $user->setRole('ROLE_ADMIN');
        $user->setStreet('Example street');
        $user->setHouseNumber(1);
        $user->setZipCode('12345');
        $user->setCity('Admin city');
        $user->setCountry('Admin country');
        $user->setPhone('+237 123456789');

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $io->success('Admin user created successfully.');

        return Command::SUCCESS;
    }
}