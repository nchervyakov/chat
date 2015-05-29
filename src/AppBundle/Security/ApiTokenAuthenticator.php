<?php
/**
 * Created with IntelliJ IDEA by Nick Chervyakov.
 * User: Nikolay Chervyakov 
 * Date: 27.05.2015
 * Time: 14:21
  */



namespace AppBundle\Security;


use AppBundle\Entity\User;
use AppBundle\Security\Core\Authentication\Token\ApiAnonymousToken;
use AppBundle\Security\Core\Authentication\Token\ApiPreAuthenticatedToken;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\Security\Core\Authentication\SimplePreAuthenticatorInterface;
use Symfony\Component\Security\Core\Authentication\Token\PreAuthenticatedToken;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class ApiTokenAuthenticator implements SimplePreAuthenticatorInterface
{

    public function authenticateToken(TokenInterface $token, UserProviderInterface $userProvider, $providerKey)
    {
        if (!$userProvider instanceof ApiTokenUserProvider) {
            throw new \InvalidArgumentException(
                sprintf(
                    'The user provider must be an instance of ApiTokenUserProvider (%s was given).',
                    get_class($userProvider)
                )
            );
        }

        $apiToken = $token->getCredentials();

        $oauthRequest = $userProvider->getOAuthRequestForToken($apiToken);

        if ($apiToken && !$oauthRequest) {
            if ($apiToken) {
                throw new UnauthorizedHttpException("Token realm=\"Please provide valid API token\"", "You provided old or incorrect token. Please refresh it.");
            }
        }

        $username = $userProvider->getUsernameForToken($apiToken);

        if (!$username) {
//            throw new AuthenticationException(
//                sprintf('API Key "%s" does not exist.', $apiToken)
//            );
            return new ApiAnonymousToken('', 'anon.', $oauthRequest, ['ROLE_API']);
        }

        try {
            /** @var User $user */
            $user = $userProvider->loadUserByUsername($username);

        } catch (UsernameNotFoundException $ex) {
            return new ApiAnonymousToken('', 'anon.', $oauthRequest, ['ROLE_API']);
        }

//        if (!$user->isActivated()) {
//            throw new Au('You are not yet activated by admin. Wi will inform you when you are activated.');
//        }

        return new ApiPreAuthenticatedToken(
            $user,
            $apiToken,
            $providerKey,
            $oauthRequest,
            array_merge($user->getRoles(), ['ROLE_API'])
        );
    }

    public function supportsToken(TokenInterface $token, $providerKey)
    {
        return $token instanceof PreAuthenticatedToken && $token->getProviderKey() === $providerKey;
    }

    public function createToken(Request $request, $providerKey)
    {
        // look for an _token query parameter
        $queryToken = $request->query->get('_token');
        $headerToken = $request->headers->get('Authorization');

        if (!$queryToken && $headerToken) {
            $parts = preg_split('/\s+/', $headerToken, 2);
            if (strtolower($parts[0]) == 'token' && $parts[1]) {
                $headerToken = $parts[1];
            }
        }

        $token = $queryToken ?: $headerToken;

        // or if you want to use an "apikey" header, then do something like this:
        // $apiKey = $request->headers->get('apikey');

        if (!$token) {
            throw new BadCredentialsException('No API token found');

            // or to just skip api key authentication
            // return null;
        }

        return new PreAuthenticatedToken(
            'anon.',
            $token,
            $providerKey,
            ['ROLE_API']
        );
    }
}