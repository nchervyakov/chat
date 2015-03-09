<?php

namespace AppBundle\Controller;

use AppBundle\Entity\User;
use AppBundle\Entity\UserRepository;
use AppBundle\Form\Type\ModelSearchFormType;
use AppBundle\Model\ModelSearch;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class SearchController
 * @package AppBundle\Controller
 * @Route("/search")
 * @Security("has_role('ROLE_CLIENT')")
 */
class SearchController extends Controller
{
    const PER_PAGE = 12;

    /**
     * @Route("", name="search_index")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request)
    {
        $page = $request->query->getInt('page', 1);

        $modelSearch = new ModelSearch();
        $form = $this->get('form.factory')->createNamedBuilder('s', new ModelSearchFormType(), $modelSearch, [
            'method' => 'GET',
            'action' => $this->generateUrl('search_index'),
        ])->getForm();

        $form->handleRequest($request);

        $data = ['form' => $form->createView()];

        if (!$form->isSubmitted() || $form->isValid()) {
            $repo = $this->getDoctrine()->getRepository('AppBundle:User');
            $qb = $repo->prepareQueryBuilderForModelSearch($modelSearch);
            $paginator = $this->get('knp_paginator');
            $pagination = $paginator->paginate($qb, $page, self::PER_PAGE);
            $data['pagination'] = $pagination;
        }

        return $this->render('Search/index.html.twig', $data);
    }
}
