<?php

namespace okpt\furnics\project\AppBundle\Filters;

use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManagerInterface;
use okpt\furnics\project\Entity\User;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Csrf\TokenStorage\TokenStorageInterface;

class RememberFilter
{
    private $entityManager;
    private $tokenStorage;
    private $userProvider;

    public function __construct(EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage, UserProviderInterface $userProvider)
    {
        $this->entityManager = $entityManager;
        $this->tokenStorage = $tokenStorage;
        $this->userProvider = $userProvider;
    }

    public function onKernelRequest(RequestEvent $event)
    {
        $request = $event->getRequest();
        $cookie = $request->cookies->get('MyWebSite');

        if ($cookie && !$this->tokenStorage->getToken()) {
            $value = unserialize(base64_decode($cookie));
            $rememberKey = $value[0];
            $username = $value[1];

            $user = $this->entityManager->getRepository(User::class)->findOneBy([
                'rememberKey' => $rememberKey,
                'lastName' => $username,
            ]);

            if ($user) {
                $token = new UsernamePasswordToken($user, 'main', $user->getRoles());
                $this->tokenStorage->setToken($token);

                $request->getSession()->set('_security_main', serialize($token));
            }
        }
    }
}
