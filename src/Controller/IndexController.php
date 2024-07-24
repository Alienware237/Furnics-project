<?php

namespace okpt\furnics\project\Controller;

use Doctrine\ORM\EntityManagerInterface;
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
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class IndexController extends AbstractController
{
    private ArticleManager $articleManager;
    private CartManager $cartManager;
    private CartItemManager $cartItemManager;
    private UserManager $userManager;
    private $entityManager;
    private $authenticationService;
    private $logger;

    public function __construct(ArticleManager $articleManager, EntityManagerInterface $entityManager, UserManager $userManager, CartManager $cartManager, CartItemManager $cartItemManager, AuthenticationService $authenticationService, LoggerInterface $logger)
    {
        $this->articleManager = $articleManager;
        $this->cartManager = $cartManager;
        $this->cartItemManager = $cartItemManager;
        $this->userManager = $userManager;
        $this->entityManager = $entityManager;
        $this->authenticationService = $authenticationService;
        $this->logger = $logger;
    }

    public function getAllArticle(ArticleManager $articleManager): void
    {
        $allArticle = $articleManager->getAllArticles();
    }
    #[Route('/index', name: 'app_index')]
    public function index(Request $request, UserPasswordHasherInterface $passwordEncoder, AuthenticationUtils $authenticationUtils, SessionInterface $session): Response
    {
        $allArticles = $this->articleManager->getAllArticles();

        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();
        $user = $this->getUser();
        $form = $this->createForm(LoginType::class);

        // Check if the user is not logged in
        if (!$user || str_contains($user->getUsername(), 'example')) {
            //echo "No real user found!";
            $form->handleRequest($request);

            // Check for a default user session
            $defaultUserId = $session->get('default_user_id');
            if ($defaultUserId) {
                $user = $this->entityManager->getRepository(User::class)->find($defaultUserId);
                if ($user) {
                    $this->authenticationService->signIn($user, new Response(), false);
                } else {
                    // Create a new default user if not found
                    $user = $this->createDefaultUser($passwordEncoder);
                    $this->authenticationService->signIn($user, new Response(), false);
                    $session->set('default_user_id', $user->getUserId());
                }
            } else {
                // Create a new default user if session doesn't exist
                $user = $this->createDefaultUser($passwordEncoder);
                $this->authenticationService->signIn($user, new Response(), false);
                $session->set('default_user_id', $user->getUserId());
            }
        }


        if ($form->isSubmitted() && $form->isValid()) {
            $email = $form->get('email')->getData();
            $password = $form->get('password')->getData();
            //$cookie = $form->get('remember_me')->getData();

            $user = $this->userManager->getUserbyEmailAndPassWD($email);

            if (is_array($user)) {
                $user = $user[0];
            }

            if ($user && $passwordEncoder->isPasswordValid($user, $password)) {
                $response = new Response();

                //$this->logger->info('Try to sign in with username: ' . $user->getUsername());
                $this->authenticationService->signIn($user, $response, true);
                //$session->set('_security_main', $user->getUserId());

                if (in_array('ROLE_ADMIN', $user->getRoles())) {
                    // Redirect to Admin dashboard
                    return $this->redirectToRoute('admin');
                }

                //return $this->redirectToRoute('app_index');
            }
        }
        $cart = $this->cartManager->getCart($user);
        if (is_array($cart)) {
            $cart = $cart[0];
        }
        $allCartItems = $this->cartManager->getAllCartArticle($cart);
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

    private function createDefaultUser(UserPasswordHasherInterface $passwordEncoder): User
    {
        $user = new User();
        $prefix = uniqid('user_', true);
        $defaultEmail = $prefix . '@example.com';
        $defaultPassword = bin2hex(random_bytes(10));

        $user->setEmail($defaultEmail);
        $user->setFirstName($prefix . 'Firstname');
        $user->setLastName($prefix . 'Lastname');
        $user->setStreet('');
        $user->setCity('');
        $user->setCountry('');
        $user->setHouseNumber(0);
        $user->setZipCode('');
        $user->setSalutation('');
        $user->setPhone('');
        $user->setPassword($passwordEncoder->hashPassword($user, $defaultPassword));

        return $this->userManager->createUser($user);
    }
}
