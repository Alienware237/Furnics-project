<?php

namespace okpt\furnics\project\Controller;

use Doctrine\ORM\EntityManagerInterface;
use okpt\furnics\project\Entity\Article;
use okpt\furnics\project\Entity\User;
use okpt\furnics\project\Form\LoginType;
use okpt\furnics\project\Services\ArticleManager;
use okpt\furnics\project\Services\CartItemManager;
use okpt\furnics\project\Services\CartManager;
use okpt\furnics\project\Services\Security\AuthenticationService;
use okpt\furnics\project\Services\UserManager;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class IndexController extends AbstractController
{
    private ArticleManager $articleManager;
    private CartManager $cartManager;
    private CartItemManager $cartItemManager;
    private $entityManager;
    private $authenticationService;
    private $logger;

    public function __construct(ArticleManager $entityManager, UserManager $userManager, CartManager $cartManager, CartItemManager $cartItemManager, AuthenticationService $authenticationService, LoggerInterface $logger)
    {
        $this->articleManager = $entityManager;
        $this->cartManager = $cartManager;
        $this->cartItemManager = $cartItemManager;
        $this->entityManager = $userManager;
        $this->authenticationService = $authenticationService;
        $this->logger = $logger;
    }

    public function getAllArticle(ArticleManager $articleManager): void
    {
        $allArticle = $articleManager->getAllArticles();
    }
    #[Route('/index', name: 'app_index')]
    public function index(Request $request, UserPasswordHasherInterface $passwordEncoder, AuthenticationUtils $authenticationUtils): Response
    {
        $allArticles = $this->articleManager->getAllArticles();

        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();
        $user = $this->getUser();
        $form = $this->createForm(LoginType::class, [
            'username' => $lastUsername,
        ]);

        if (!$user) {

            $form->handleRequest($request);

            $user = new User();

            if ($form->isSubmitted() && $form->isValid()) {
                $email = $form->get('email')->getData();
                $password = $form->get('password')->getData();
                //$cookie = $form->get('remember_me')->getData();

                $user = $this->entityManager->getUserbyEmailAndPassWD($email);

                if (is_array($user)) {
                    $user = $user[0];
                }

                if ($user && $passwordEncoder->isPasswordValid($user, $password)) {
                    $response = new Response();

                    //$this->logger->info('Try to sign in with username: ' . $user->getUsername());
                    $this->authenticationService->signIn($user, true, $response);

                    //return $this->redirectToRoute('app_index');
                }
            }
        }
        $cart = $this->cartManager->getCart($user);
        if (is_array($cart)) {
            $cart = $cart[0];
        }
        $allCartItems = $this->cartManager->getAllCartArticle($cart);
        //print_r($user);
        return $this->render('index/index.html.twig', [
            'controller_name' => 'IndexController',
            'articles' => $allArticles,
            'user' => $user,
            'cart' => $cart,
            'cartItems' => $allCartItems,
            'form' => $form->createView(),
            'error' => $error,
        ]);
    }
}
