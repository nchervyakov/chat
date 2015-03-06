<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Class DefaultController
 * @package AppBundle\Controller
 */
class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction()
    {
        $checker = $this->container->get('security.authorization_checker');
        if ($checker->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            if ($checker->isGranted('ROLE_MODEL')) {
                return $this->redirect($this->generateUrl('stat_index'));

            } else {
                return $this->redirect($this->generateUrl('chat'));
            }
        }

        return $this->render(':Default:index.html.twig');
    }
}
