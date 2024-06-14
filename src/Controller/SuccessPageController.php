<?php

namespace okpt\furnics\project\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class SuccessPageController extends AbstractController
{
    #[Route('/success/page', name: 'app_success_page')]
    public function index(): Response
    {
        return $this->render('success_page/index.html.twig', [
            'controller_name' => 'SuccessPageController',
        ]);
    }
}
