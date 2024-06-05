<?php

namespace okpt\furnics\project\AppBundle\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class SumOfArticlesPrices extends AbstractExtension
{
    public function getFilters()
    {
        return [
            new TwigFilter('total_price', [$this, 'sumOfArticlesPrices']),
        ];
    }

    public function sumOfArticlesPrices(array $allArticles)
    {
        $allArticlesPrices = 0;
        foreach ($allArticles as $articleItem) {
            $articlePrice = $articleItem['article']->getArticlePrice();
            $allArticlesPrices += ($articlePrice * $articleItem['quantity']);
        }
        return $allArticlesPrices;
    }
}