<?php

namespace okpt\furnics\project\Controller;

use Doctrine\ORM\EntityManagerInterface;
use okpt\furnics\project\Entity\User;
use okpt\furnics\project\Form\LoginType;
use okpt\furnics\project\Services\Security\AuthenticationService;
use okpt\furnics\project\Services\UserManager;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    private $entityManager;
    private $authenticationService;
    private $logger;

    public function __construct(UserManager $entityManager, AuthenticationService $authenticationService, LoggerInterface $logger)
    {
        $this->entityManager = $entityManager;
        $this->authenticationService = $authenticationService;
        $this->logger = $logger;
    }

    #[Route('/login', name: 'app_login')]
    public function login(Request $request, UserPasswordHasherInterface $passwordEncoder, AuthenticationUtils $authenticationUtils): Response
    {
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        $form = $this->createForm(LoginType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $email = $form->get('email')->getData();
            $password = $form->get('password')->getData();
            ;
            //$cookie = $form->get('remember_me')->getData();

            $user = $this->entityManager->getUserbyEmailAndPassWD($email);

            if (is_array($user)) {
                $user = $user[0];
            }

            if ($user && $passwordEncoder->isPasswordValid($user, $password)) {
                $response = new Response();

                $this->authenticationService->signIn($user, true, $response);

                return $this->redirectToRoute('app_index');
            }
            $this->addFlash('error', 'Invalid credentials');
        }
        return $this->render('security/login.html.twig', [
            'form' => $form->createView(),
            'error' => $error,
        ]);
    }

    /**
     * @Route("/logout", name="logout")
     */
    public function logout(Request $request): Response
    {
        $response = new Response();
        $this->authenticationService->signOut($response);

        return $this->redirectToRoute('homepage');
    }
}
