<?php
/**
 * Created with IntelliJ IDEA by Nick Chervyakov.
 * User: Nikolay Chervyakov 
 * Date: 14.05.2015
 * Time: 18:24
  */



namespace AppBundle\SocketIO;


use AppBundle\Entity\User;
use Symfony\Component\DependencyInjection\ContainerAware;

class SocketIOTokenStorage extends ContainerAware
{
    protected $token;

    public function getToken()
    {
        if ($this->token) {
            return $this->token;
        }

        $userToken = $this->container->get('security.token_storage')->getToken();
        if (!$userToken || !($user = $userToken->getUser())) {
            $this->token = null;
            return null;
        }

        $session = $this->container->get('session');

        if ($session->get('socket_io.token')) {
            $this->token = $session->get('socket_io.token');
            return $this->token;
        }

        /** @var User $user */
        $this->token = sha1($user->getId() . uniqid() . '_' . time());

        $session->set('socket_io.token', $this->token);

        return $this->token;
    }
}