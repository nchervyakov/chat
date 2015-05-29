<?php
/**
 * Created with IntelliJ IDEA by Nick Chervyakov.
 * User: Nikolay Chervyakov 
 * Date: 26.05.2015
 * Time: 18:41
  */



namespace AppBundle\Security;


use AppBundle\Entity\OAuthRequest;
use Doctrine\ORM\EntityManager;
use HWI\Bundle\OAuthBundle\Security\Core\Exception\AccountNotLinkedException;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpFoundation\Request;

/**
 * Manages OAuth interactions.
 */
class OAuthService extends ContainerAware
{
    protected static $providerRedirectUris = [
        'facebook' => 'https://www.facebook.com/connect/login_success.html'
    ];

    /**
     * @param OAuthRequest $oauthRequest
     * @return OAuthRequest
     */
    public function registerOAuthRequest(OAuthRequest $oauthRequest)
    {
        $providerName = $oauthRequest->getProviderName();
        $code = $oauthRequest->getCode();

        if (!$providerName || !$code) {
            throw new \InvalidArgumentException('OAuth request must contain provider name and auth code.');
        }

        $resourceOwner = $this->container->get('hwi_oauth.resource_ownermap.secured_area')->getResourceOwnerByName($providerName);

        if (!$resourceOwner) {
            throw new \InvalidArgumentException('Invalid provider name: ' . $providerName);
        }

        if ($oauthRequest->getAccessToken()) {
            return $oauthRequest;
        }

        $request = new Request();
        $request->query->set('code', $code);

        $redirectUri = $oauthRequest->getRedirectUri() ?: self::$providerRedirectUris[$providerName];
        $response = $resourceOwner->getAccessToken($request, $redirectUri);

        $oauthRequest->setAccessToken($response['access_token']);
        $expires = new \DateTime();
        $expires->modify('+'.$response['expires'].' seconds');
        $oauthRequest->setAccessTokenExpires($expires);

        $oauthRequest->setToken($this->container->get('fos_user.util.token_generator')->generateToken());

        $userResponse = $resourceOwner->getUserInformation($response);

        $oauthUserId = $userResponse->getUsername();
        $oauthRequest->setOauthUserId($oauthUserId);
        $oauthRequest->setOauthData([
            'email' => $userResponse->getEmail(),
            'username' => $userResponse->getUsername(),
            'nickname' => $userResponse->getNickname(),
            'real_name' => $userResponse->getRealName(),
            'profile_picture' => $userResponse->getProfilePicture()
        ]);

        /** @var EntityManager $em */
        $em = $this->container->get('doctrine')->getManager();

        // Try to find current user by oauth user id
        try {
            $user = $this->container->get('hwi_oauth.user.provider.entity.secured_area')->loadUserByOAuthUserResponseOrToken($userResponse);
        } catch (AccountNotLinkedException $ex) {
            $user = null;
        }

        $oauthRequest->setUser($user);

        $em->persist($oauthRequest);
        $em->flush();

        return $oauthRequest;
    }
}