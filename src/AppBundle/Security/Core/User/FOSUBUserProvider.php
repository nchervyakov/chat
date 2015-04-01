<?php
/**
 * Created with IntelliJ IDEA by Nick Chervyakov.
 * User: Nikolay Chervyakov 
 * Date: 01.04.2015
 * Time: 11:54
  */



namespace AppBundle\Security\Core\User;


use AppBundle\Entity\User;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use HWI\Bundle\OAuthBundle\Security\Core\Authentication\Token\OAuthToken;
use HWI\Bundle\OAuthBundle\Security\Core\Exception\AccountNotLinkedException;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class FOSUBUserProvider extends \HWI\Bundle\OAuthBundle\Security\Core\User\FOSUBUserProvider implements ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    public function loadUserByOAuthUserResponseOrToken(UserResponseInterface $response, OAuthToken $token = null)
    {
        try {
            return parent::loadUserByOAuthUserResponse($response);

        } catch (AccountNotLinkedException $ex) {
            if ($token->hasAttribute('activation_token') && $token->getAttribute('activation_token')) {
                /** @var User $user */
                $user = $this->userManager->findUserBy(['activationToken' => $token->getAttribute('activation_token')]);

                if ($user) {
                    $user->setFacebookId($response->getUsername());
                    return $user;
                }
            }

            throw $ex;
        }
    }

    /**
     * Sets the Container.
     *
     * @param ContainerInterface|null $container A ContainerInterface instance or null
     *
     * @api
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }
}