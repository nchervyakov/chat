<?php

namespace AppBundle\Controller;

use AppBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class UserController
 * @package AppBundle\Controller
 * @Route("/user")
 * @Security("has_role('ROLE_USER')")
 */
class UserController extends Controller
{
    /**
     * @Route("/{user_id}", name="user_show")
     * @ParamConverter("user", class="AppBundle:User", options={"id": "user_id"})
     * @param User $user
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showAction(User $user)
    {
        return $this->render(':User:show.html.twig', [
            'user' => $user
        ]);
    }

    /**
     * @Route("/activate-via-facebook/{activationToken}", name="user_activate_via_social_network")
     * @param string $activationToken
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @Security("not has_role('ROLE_USER')")
     */
    public function activateViaFacebookAction($activationToken)
    {
        $activationToken = trim($activationToken);
        $user = $this->getDoctrine()->getRepository('AppBundle:User')->findOneBy(['activationToken' => $activationToken]);
        if (!$user || !$user->isEnabled()) {
            throw new NotFoundHttpException();
        }

        if (!$user->needToActivate()) {
            throw new HttpException(400, "The user is already activated.");
        }

        return $this->redirectToRoute('oauth_service_redirect', [
            'activation_token' => $activationToken,
            'service' => 'facebook'
        ]);
    }
}
