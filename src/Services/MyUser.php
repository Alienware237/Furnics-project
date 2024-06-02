<?php

namespace okpt\furnics\project\Services;

use okpt\furnics\project\Entity\User;

class MyUser extends User
{
    public function signIn($user, $remember = false)
    {
        $this->setAuthenticated(true);
        $this->setAttribute('user_id', $user->getId());

        if ($remember) {
            $rememberKey = $this->generateRandomKey();
            $user->setRememberKey($rememberKey);
            $this->save();

            $value = base64_encode(serialize([$rememberKey, $user->getUsername()]));
            sfContext::getInstance()->getResponse()->setCookie('MyWebSite', $value, time() + 3600 * 24 * 15, '/');
        }
    }

    public function signOut()
    {
        $this->setAuthenticated(false);
        sfContext::getInstance()->getResponse()->setCookie('MyWebSite', '', time() - 3600, '/');
    }

    private function generateRandomKey()
    {
        return bin2hex(random_bytes(16));
    }
}
