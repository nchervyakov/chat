<?php
/**
 * Created with IntelliJ IDEA by Nick Chervyakov.
 * User: Nikolay Chervyakov 
 * Date: 27.05.2015
 * Time: 18:11
 */

namespace AppBundle\Security\Core\Authentication\Token;


use AppBundle\Entity\OAuthRequest;

interface ApiTokenInterface
{
    public function getOAuthRequest();
    public function setOAuthRequest(OAuthRequest $oauthRequest);
}