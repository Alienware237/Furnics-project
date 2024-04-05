<?php

namespace okpt\furnics\project\Controller;

use okpt\furnics\project\Entity\Article;
use okpt\furnics\project\Services\ArticleManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ArticleController extends AbstractController
{
    /**
     * @Route("/product/create", name="create_product")
     */
    public function createProduct(ArticleManager $articleManager): Response
    {
        $articleManager->createArticle('Article Name', 'Article Description', '19.99', 10, 'Category');

        return new Response('Article created successfully!');
    }
}
