<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class StatController
 * @package AppBundle\Controller
 * @Route("/stat")
 * @Security("has_role('ROLE_MODEL')")
 */
class StatController extends Controller
{
    const PER_PAGE = 10;

    /**
     * @Route("/", name="stat_index")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request)
    {
        $page = $request->query->getInt('page', 1);
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $userRepo = $this->getDoctrine()->getRepository('AppBundle:User');
        $conversationsQB = $userRepo->createUserConversationsQueryBuilder($user);

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate($conversationsQB, $page, self::PER_PAGE);

        return $this->render('Stat/index.html.twig', [
            'pagination' => $pagination
        ]);
    }
}
