<?php

namespace okpt\furnics\project\Controller;

use okpt\furnics\project\Entity\User;
use okpt\furnics\project\Form\RegistrationType;
use okpt\furnics\project\Services\Security\AuthenticationService;
use okpt\furnics\project\Services\UserManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class UserController extends AbstractController
{

    private UserManager $userManager;
    private $authenticationService;

    public function __construct(UserManager $entityManager, AuthenticationService $authenticationService)
    {
        $this->userManager = $entityManager;
        $this->authenticationService = $authenticationService;
    }

    #[Route('/user', name: 'app_user')]
    public function index(): Response
    {
        return $this->render('user/index.html.twig', [
            'controller_name' => 'UserController',
        ]);
    }

    #[Route('/user/login', name: 'app_user_login')]
    public function executeLogin(Request $request)
    {
        $username = $request->get('username');
        $password = $request->get('password');
        $remember = $request->get('remember_me');
        $email = $request->get('email');


        $user = new User();
        $user = $this->userManager->getUserbyEmailAndPassWD($email, $password);

        if ($user && password_verify($password, $user->getPassword())) {
            $this->get('security.token_storage')->setToken(new UsernamePasswordToken($user, 'main', $user->getRole()));

            if ($email) {
                $rememberKey = $this->generateRandomKey();
                $user->setCookie($rememberKey);
                $this->userManager->flush();

                $value = base64_encode(serialize([$rememberKey, $user->getFirstName()]));
                $response = new Response();
                $response->headers->setCookie(new Cookie('MyWebSite', $value, time() + 3600 * 24 * 15, '/', null, false, true));
                $response->sendHeaders();
            }

            return $this->redirectToRoute('homepage');
        } else {
            $this->addFlash('error', 'Invalid credentials');
            return $this->redirectToRoute('login');
        }
    }

    private function generateRandomKey()
    {
        return bin2hex(random_bytes(16));
    }


    public function executeLogout(Request $request)
    {
        $this->get('security.token_storage')->setToken(null);
        $this->get('session')->invalidate();
        $response = new Response();
        $response->headers->clearCookie('MyWebSite');
        $response->sendHeaders();

        return $this->redirectToRoute('homepage');
    }


    #[Route('/user/register', name: 'app_user_register')]
    public function register(Request $request, UserPasswordHasherInterface $passwordEncoder): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationType::class, $user);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword(
                $passwordEncoder->hashPassword(
                    $user,
                    $form->get('password')->getData()
                )
            );

            $this->userManager->createUser($user);

            return $this->redirectToRoute('app_login');
        }

        return $this->render('registration/register.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/logout', name: 'app_logout', methods: ['POST'])]
    public function logout(Request $request): Response
    {
        $response = new Response();
        $this->authenticationService->signOut($response);

        return $this->redirectToRoute('app_index');
    }

}
