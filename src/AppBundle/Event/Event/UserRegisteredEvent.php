<?php
/**
 * Created by IntelliJ IDEA.
 * User: Nikolay Chervyakov 
 * Date: 20.03.2015
 * Time: 14:32
 */


namespace AppBundle\Event\Event;


use AppBundle\Entity\User;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use Symfony\Component\EventDispatcher\Event;

class UserRegisteredEvent extends Event
{
    /**
     * @var User
     */
    protected $user;

    /**
     * @var UserResponseInterface
     */
    protected $userInformation;

    function __construct(User $user, UserResponseInterface $userInformation)
    {
        $this->user = $user;
        $this->userInformation = $userInformation;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @return UserResponseInterface
     */
    public function getUserInformation()
    {
        return $this->userInformation;
    }
}