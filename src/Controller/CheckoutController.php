<?php

namespace okpt\furnics\project\Controller;

use okpt\furnics\project\Services\CartManager;
use okpt\furnics\project\Services\UserManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CheckoutController extends AbstractController
{
    private $userManager;
    private $cartManager;

    public function __construct(UserManager $userManager, CartManager $cartManager)
    {
        $this->userManager = $userManager;
        $this->cartManager = $cartManager;
    }
    #[Route('/checkout', name: 'app_checkout')]
    public function index(): Response
    {
        $user = $this->getUser();

        $user = $this->userManager->getUserbyEmailAndPassWD($user->getUserIdentifier());

        $cart = $this->cartManager->getCart($user->getUserId());
        if (is_array($cart)) {
            $cart = $cart[0];
        }
        $allCartItems = $this->cartManager->getAllCartArticle($cart->getCartId());
        return $this->render('checkout/index.html.twig', [
            'controller_name' => 'CheckoutController',
            'user' => $user,
            'allCartItems' => $allCartItems

        ]);
    }
}
