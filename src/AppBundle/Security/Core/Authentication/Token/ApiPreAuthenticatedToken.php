<?php
/**
 * Created with IntelliJ IDEA by Nick Chervyakov.
 * User: Nikolay Chervyakov 
 * Date: 27.05.2015
 * Time: 18:19
  */



namespace AppBundle\Security\Core\Authentication\Token;


use AppBundle\Entity\OAuthRequest;
use AppBundle\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\PreAuthenticatedToken;

class ApiPreAuthenticatedToken extends PreAuthenticatedToken implements ApiTokenInterface
{
    /**
     * @var OAuthRequest
     */
    protected $oauthRequest;

    /**
     * @param array|\Symfony\Component\Security\Core\Role\RoleInterface[]|User $user
     * @param $credentials
     * @param $providerKey
     * @param null $oauthRequest
     * @param array $roles
     */
    public function __construct($user, $credentials, $providerKey, $oauthRequest = null, array $roles = array())
    {
        parent::__construct($user, $credentials, $providerKey, $roles);
        $this->oauthRequest = $oauthRequest;
    }

    public function getOAuthRequest()
    {
        return $this->oauthRequest;
    }

    public function setOAuthRequest(OAuthRequest $oauthRequest)
    {
        $this->oauthRequest = $oauthRequest;

        return $this->oauthRequest;
    }

    public function serialize()
    {
        return array_merge([
            is_object($this->oauthRequest) ? clone $this->oauthRequest : $this->oauthRequest,
            parent::serialize()
        ]);
    }

    public function unserialize($serialized)
    {
        list($this->oauthRequest, $parentStr) = unserialize($serialized);
        parent::unserialize($parentStr);
    }
}