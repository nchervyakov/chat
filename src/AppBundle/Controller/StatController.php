<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/**
 * Class StatController
 * @package AppBundle\Controller
 * @Route("/stat")
 * @Security("has_role('ROLE_USER') and has_role('ROLE_MODEL')")
 */
class StatController extends Controller
{
    /**
     * @Route("", name="stat_index")
     */
    public function indexAction()
    {
        return $this->render('Stat/index.html.twig');
    }
}
