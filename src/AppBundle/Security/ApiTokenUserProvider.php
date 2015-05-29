<?php
/**
 * Created with IntelliJ IDEA by Nick Chervyakov.
 * User: Nikolay Chervyakov 
 * Date: 27.05.2015
 * Time: 14:29
  */



namespace AppBundle\Security;


use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class ApiTokenUserProvider extends ContainerAware implements UserProviderInterface
{
    public function getUsernameForToken($token)
    {
        $oauthRequest = $this->getOAuthRequestForToken($token);

        if ($oauthRequest && ($user = $oauthRequest->getUser()) && $user->getUsername()) {
            return $user->getUsername();
        }

        return null;
    }

    /**
     * @param $token
     * @return \AppBundle\Entity\OAuthRequest
     */
    public function getOAuthRequestForToken($token)
    {
        /** @var EntityManager $em */
        $em = $this->getManager();
        $oauthRequest = $em->getRepository('AppBundle:OAuthRequest')->findOneBy(['token' => $token]);
        return $oauthRequest;
    }

    /**
     * Loads the user for the given username.
     *
     * This method must throw UsernameNotFoundException if the user is not
     * found.
     *
     * @param string $username The username
     *
     * @return UserInterface
     *
     * @see UsernameNotFoundException
     *
     * @throws UsernameNotFoundException if the user is not found
     */
    public function loadUserByUsername($username)
    {
        /** @var EntityManager $em */
        $em = $this->getManager();
        $user = $em->getRepository('AppBundle:User')->findOneBy(['username' => $username]);

        if (!$user) {
            throw new UsernameNotFoundException;
        }

        return $user;
    }

    /**
     * Refreshes the user for the account interface.
     *
     * It is up to the implementation to decide if the user data should be
     * totally reloaded (e.g. from the database), or if the UserInterface
     * object can just be merged into some internal array of users / identity
     * map.
     *
     * @param UserInterface $user
     *
     * @return UserInterface
     *
     * @throws UnsupportedUserException if the account is not supported
     */
    public function refreshUser(UserInterface $user)
    {
        // this is used for storing authentication in the session
        // but in this example, the token is sent in each request,
        // so authentication can be stateless. Throwing this exception
        // is proper to make things stateless
        throw new UnsupportedUserException();
    }

    /**
     * Whether this provider supports the given user class.
     *
     * @param string $class
     *
     * @return bool
     */
    public function supportsClass($class)
    {
        return 'AppBundle\\Entity\\User' === $class;
    }

    /**
     * @return \Doctrine\Common\Persistence\ObjectManager|object|EntityManager
     */
    protected function getManager()
    {
        return $this->container->get('doctrine')->getManager();
    }
}