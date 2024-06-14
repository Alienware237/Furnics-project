<?php

namespace okpt\furnics\project\Services\Security;

use Doctrine\ORM\EntityManagerInterface;
use okpt\furnics\project\Entity\User;
use okpt\furnics\project\Services\UserManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

class AuthenticationService
{
    private $entityManager;
    private $requestStack;
    private $tokenStorage;
    private $userProvider;

    private $logger;

    public function __construct(UserManager $entityManager, RequestStack $requestStack, TokenStorageInterface $tokenStorage, UserProviderInterface $userProvider, LoggerInterface $logger)
    {
        $this->entityManager = $entityManager;
        $this->requestStack = $requestStack;
        $this->tokenStorage = $tokenStorage;
        $this->userProvider = $userProvider;
        $this->logger = $logger;
    }

    public function signIn(User $user, bool $remember = false, Response $response)
    {
        // Set the authentication token
        $token = new UsernamePasswordToken($user, 'main', $user->getRoles());
        $this->tokenStorage->setToken($token);



        // Save the token in the session
        $session = $this->requestStack->getSession();
        $session->set('_security_main', serialize($token));

        if ($remember) {
            // Generate a remember key
            $rememberKey = bin2hex(random_bytes(16));
            $user->setCookie($rememberKey);
            $this->entityManager->updateUser($user);

            $userName = $user->getFirstName();

            //$this->logger->info(`SignIn The user: $userName`);

            // Set the remember key in a cookie
            $value = base64_encode(serialize([$rememberKey, $user->getEmail()]));

            $response->headers->setCookie(new Cookie('MyWebSite', $value, time() + 3600 * 24 * 15, '/', null, false, true));
        }
    }

    public function signOut(Response $response)
    {
        $this->tokenStorage->setToken(null);

        // Invalidate the session
        $session = $this->requestStack->getSession();
        $session->invalidate();

        // Clear the remember cookie
        $response->headers->clearCookie('MyWebSite');
    }
}
