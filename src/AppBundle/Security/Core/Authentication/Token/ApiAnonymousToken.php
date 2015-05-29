<?php
/**
 * Created with IntelliJ IDEA by Nick Chervyakov.
 * User: Nikolay Chervyakov 
 * Date: 27.05.2015
 * Time: 18:17
  */



namespace AppBundle\Security\Core\Authentication\Token;


use AppBundle\Entity\OAuthRequest;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;

class ApiAnonymousToken extends AnonymousToken implements ApiTokenInterface
{
    /**
     * @var OAuthRequest
     */
    protected $oauthRequest;

    public function __construct($key, $user, $oauthRequest = null, array $roles = array())
    {
        parent::__construct($key, $user, $roles);
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
        return array_merge([$this->oauthRequest, parent::serialize()]);
    }

    public function unserialize($serialized)
    {
        list($this->oauthRequest, $parentStr) = unserialize($serialized);
        parent::unserialize($parentStr);
    }
}