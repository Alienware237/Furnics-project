<?php

namespace okpt\furnics\project\Controller;

use Doctrine\ORM\EntityManagerInterface;
use okpt\furnics\project\Entity\Article;
use okpt\furnics\project\Services\ArticleManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class IndexController extends AbstractController
{
    private ArticleManager $articleManager;

    public function __construct(ArticleManager $entityManager)
    {
        $this->articleManager = $entityManager;
    }

    public function getAllArticle (ArticleManager $articleManager)
    {
        $allArticle = $articleManager->getAllArticles();
    }
    #[Route('/index', name: 'app_index')]
    public function index(): Response
    {
        $allArticles = $this->articleManager->getAllArticles();

        print_r($allArticles);
        return $this->render('index/index.html.twig', [
            'controller_name' => 'IndexController',
            'articles' => $allArticles
        ]);
    }
}
