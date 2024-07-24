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
    private ArticleManager $articleManager;

    public function __construct(
        ArticleManager $articleManager
    ) {
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

        $articleDetail1 = [
            'description' => 'Article Description 2 for Test',
            'sizeAndQuantity' => [
                'M' => 5,
                'X' => 8
            ]
        ];

        $articleDetail2 = [
            'description' => 'Article Description 2 for Test',
            'sizeAndQuantity' => [
                'M' => 3,
                'X' => 8
            ]
        ];
        $articleDetail3 = [
            'sizeAndQuantity' => [
                'M' => 8,
                'X' => 6
            ]
        ];

        $this->articleManager->createArticle('First article', json_encode($articleDetail1), 19.99, 'Category1', json_encode(["uploads/product-item1.jpg"]));
        $this->articleManager->createArticle('Second article', json_encode($articleDetail2), 17.99, 'Category2', json_encode(["uploads/product-item2.jpg"]));
        $this->articleManager->createArticle('Third article', json_encode($articleDetail3), 18.99, 'Category3', json_encode(["uploads/product-item3.jpg"]));

        return Command::SUCCESS;
    }
}
