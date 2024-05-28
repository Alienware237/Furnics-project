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

    public function createArticle(string $name, string $description, string $price, string $category, $image): void
    {
        $article = new Article();
        $article->setArticleName($name);
        $article->setDescription($description);
        $article->setArticlePrice($price);
        $article->setArticleCategory($category);
        $article->setArticleImages($image);
        $article->setCreatedAt(new \Datetime());
        $article->setUpdatedAt(new \DateTime());


        $this->entityManager->persist($article);
        $this->entityManager->flush();
    }

    public function updateArticle(Article $article, string $name, string $description, string $price, string $category, $image): void {
        $article->setArticleName($name);
        $article->setDescription($description);
        $article->setArticlePrice($price);
        $article->setArticleCategory($category);
        $article->setArticleImages($image);
        $article->setUpdatedAt(new \DateTime());
        $this->entityManager->persist($article);
        $this->entityManager->flush();
    }
    public function deleteArticle(Article $article): void {
        $this->entityManager->remove($article);
    }

    public function getArticleById(int $id): Article {
        return $this->entityManager->find(Article::class, $id);
    }

    public function getArticleByName(string $name): Article {
        return $this->entityManager->find(Article::class, $name);
    }
    public function getArticleByCategory(string $category): Article {
        return $this->entityManager->find(Article::class, $category);
    }
    public function getAllArticles(): array {
        return $this->entityManager->getRepository(Article::class)->findAll();
    }
}
