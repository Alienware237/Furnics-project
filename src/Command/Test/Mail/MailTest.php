<<<<<<< HEAD
<?php

namespace okpt\furnics\project\Command\Test\Mail;

use okpt\furnics\project\Services\ArticleManager;
use okpt\furnics\project\Services\MailService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:mail-send',
    description: 'Command to sent new mail',
)]
class MailTest extends Command
{
    private MailService $mailService;
    public function __construct(
        MailService $mailService
    ) {
        parent::__construct();
        $this->mailService = $mailService;
    }

    protected function configure(): void
    {
        $this
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $arg1 = $input->getArgument('arg1');

        if ($arg1) {
            $io->note(sprintf('You passed an argument: %s', $arg1));
        }

        if ($input->getOption('option1')) {
            // ...
        }

        $this->mailService->sendEmailTest(
            'piamkevin67@gmail.com',
            'Test Subject',
            'This is the body of the email.'
        );


        $io->success('You have a new command! Now make it your own! Pass --help to see your options.');



        return Command::SUCCESS;
    }
}
=======
<?php

namespace okpt\furnics\project\Command\Test\Mail;

use okpt\furnics\project\Services\ArticleManager;
use okpt\furnics\project\Services\MailService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:mail-send',
    description: 'Command to sent new mail',
)]
class MailTest extends Command
{

    private MailService $mailService;
    public function __construct(
        MailService $mailService
    ) {
        parent::__construct();
        $this->mailService = $mailService;
    }

    protected function configure(): void
    {
        $this
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $arg1 = $input->getArgument('arg1');

        if ($arg1) {
            $io->note(sprintf('You passed an argument: %s', $arg1));
        }

        if ($input->getOption('option1')) {
            // ...
        }

        $this->mailService->sendEmailTest(
            'piamkevin67@gmail.com',
            'Test Subject',
            'This is the body of the email.'
        );


        $io->success('You have a new command! Now make it your own! Pass --help to see your options.');



        return Command::SUCCESS;
    }
}
>>>>>>> 81c2f77de4f19d374190ed90d5e02a09bf0fba5b
