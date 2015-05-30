<?php
/**
 * Created with IntelliJ IDEA by Nick Chervyakov.
 * User: Nikolay Chervyakov 
 * Date: 26.05.2015
 * Time: 17:53
  */



namespace AppBundle\Controller\Api;


use AppBundle\Entity\OAuthRequest;
use AppBundle\Entity\User;
use FOS\RestBundle\Controller\Annotations as FOSRest;
use FOS\RestBundle\Controller\FOSRestController;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class ChatController
 * @package AppBundle\Controller\Api
 *
 * @FOSRest\NamePrefix("api_v1_")
 * @FOSRest\Route("/api/v1")
 */
class OAuthRequestController extends FOSRestController
{
    /**
     * @ApiDoc(
     *      resource=true,
     *      description="Returns a OAuthRequest object.",
     *      section="Users",
     *      output="AppBundle\Entity\OAuthRequest"
     * )
     *
     * @FOSRest\View(serializerEnableMaxDepthChecks=true, serializerGroups={"user_read"})
     * @FOSRest\Get("/oauth_requests/{token}", name="api_v1_get_oauth_request", requirements={"_format": "json|xml"}, defaults={"_format": "json"})
     *
     * @param int $token Chat API access token as string
     * @return \AppBundle\Entity\OAuthRequest
     */
    public function getOauthRequestAction($token)
    {
        /** @var User $user */
        $user = $this->getUser();
        $oauthRequest = $this->getDoctrine()->getRepository('AppBundle:OAuthRequest')->findOneBy(['token' => $token]);

        if (!$oauthRequest || ($user && $oauthRequest->getUser() != $user)) {
            throw $this->createNotFoundException("OAuth Request Not found");
        }

        return $this->view($oauthRequest);
    }

    /**
     * @ApiDoc(
     *      resource=true,
     *      description="Creates a new OAuthRequest object if request to oAuth server succeeds.",
     *      section="Users",
     *      parameters={
     *          {"name"="provider_name", "dataType"="string", "required"=true, "description"="e.g. 'facebook'"},
     *          {"name"="code", "dataType"="string", "required"=true, "description"="The code received by OAuth provider."},
     *          {"name"="redirect_uri", "dataType"="string", "required"=false, "description"="If used non-standard redirect URI for the given provider, put it in this field (to match when server will get the access_token)"}
     *      },
     *      output="AppBundle\Entity\OAuthRequest"
     * )
     *
     * @FOSRest\View(serializerEnableMaxDepthChecks=true, serializerGroups={"user_read"})
     * @FOSRest\Post("/oauth_requests", name="api_v1_post_oauth_request", requirements={"_format": "json|xml"}, defaults={"_format": "json"})
     *
     * @param Request $request
     * @return \Symfony\Component\Form\Form
     */
    public function postOauthRequestAction(Request $request)
    {
        $oauthRequest = new OAuthRequest();
        $form = $this->get('form.factory')->createNamed('', 'oauth_request', $oauthRequest, [
            'csrf_protection' => false,
            'method' => 'POST'
        ]);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $this->get('app.oauth')->registerOAuthRequest($oauthRequest);
            $view = $this->view($oauthRequest);
            $view->getSerializationContext()
                ->setGroups(['user_read'])
                ->enableMaxDepthChecks();
            return $view;
        }

        $view = $this->view($form);
        $view->setStatusCode(400);
        return $view;
    }
}