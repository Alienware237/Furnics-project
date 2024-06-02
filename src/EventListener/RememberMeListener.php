<?php

namespace okpt\furnics\project\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use okpt\furnics\project\Entity\User;
use okpt\furnics\project\Services\UserManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Log\Logger;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class RememberMeListener
{
    private $entityManager;
    private $tokenStorage;
    private $userProvider;

    private $logger;

    public function __construct(UserManager $entityManager, TokenStorageInterface $tokenStorage, UserProviderInterface $userProvider, LoggerInterface $logger)
    {
        $this->entityManager = $entityManager;
        $this->tokenStorage = $tokenStorage;
        $this->userProvider = $userProvider;
    }

    public function onKernelRequest(RequestEvent $event)
    {
        $request = $event->getRequest();
        $remember = $request->cookies->get('MyWebSite');

        if ($remember && !$this->tokenStorage->getToken()) {
            $value = unserialize(base64_decode($remember));
            $rememberKey = $value[0];
            $email = $value[1];

            $this->logger->info("Remember Me $rememberKey");

            $user = $this->entityManager->getUserByEmailAndCookie($email, $rememberKey,);

            if ($user) {
                if (is_array($user)) {
                    $user = $user[0];
                }
                $token = new UsernamePasswordToken($user,'main', $user->getRoles());
                $this->tokenStorage->setToken($token);

                $request->getSession()->set('_security_main', serialize($token));
            }
        }
    }
}