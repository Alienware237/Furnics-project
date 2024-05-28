<?php
// src/Service/ProductManager.php

namespace okpt\furnics\project\Services;

use DateTime;
use okpt\furnics\project\Entity\Article;
use Doctrine\ORM\EntityManagerInterface;
use function Symfony\Component\Clock\now;

class ArticleManager
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function createArticle(string $name, string $description, string $price, int $numberInStock, string $category): void
    {
        $article = new Article();
        $article->setArticleName($name);
        $article->setDescription($description);
        $article->setArticlePrice($price);
        $article->setNumberInStock($numberInStock);
        $article->setArticleCategory($category);
        $article->setCreatedAt(new \Datetime());
        $article->setUpdatedAt(new \DateTime());


        $this->entityManager->persist($article);
        $this->entityManager->flush();
    }
}
