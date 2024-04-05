<?php

namespace okpt\furnics\project\Command\Test\Article;

use okpt\furnics\project\Services\ArticleManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:create-article',
    description: 'Command to create new article',
)]
class createArticleTestCommand extends Command
{
    private $articleManager;

    public function __construct(
        ArticleManager $articleManager
    )
    {
        parent::__construct();
        $this->articleManager = $articleManager;
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

        $io->success('You have a new command! Now make it your own! Pass --help to see your options.');

        $this->articleManager->createArticle('Article Name', 'Article Description', '19.99', 10, 'Category');


        return Command::SUCCESS;
    }
}