<?php

namespace AppBundle\Controller;

use AppBundle\Entity\User;
use AppBundle\Form\Type\ModelSearchFormType;
use AppBundle\Model\ModelSearch;
use Knp\Bundle\PaginatorBundle\Pagination\SlidingPagination;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
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

        // Try to fetch saved to session search parameters
        if ($model = $this->get('session')->get('search.model')) {
            $modelSearchModel = $model;

        } else {
            $modelSearchModel = new ModelSearch();
        }

        $form = $this->get('form.factory')->createNamedBuilder('s', new ModelSearchFormType(), $modelSearchModel, [
            'method' => 'GET',
            'action' => $this->generateUrl('search_index'),
        ])->getForm();

        $form->handleRequest($request);
        $data = ['form' => $form->createView()];

        if (!$form->isSubmitted() || $form->isValid()) {
            if (!$request->isXmlHttpRequest()) {
                $modelSearchModel->setOffline(false);
            }
            if ($form->isSubmitted()) {
                // Save requested search parameters to session
                $this->get('session')->set('search.model', $modelSearchModel);
            }
             //dump($modelSearchModel);
            $repo = $this->getDoctrine()->getRepository('AppBundle:User');
            $qb = $repo->prepareQueryBuilderForModelSearch($modelSearchModel, 15);
            $qb->leftJoin('u.thumbnail', 'tn')->addSelect('tn');  // prefetch user thumbnails

            $paginator = $this->get('knp_paginator');
            /** @var SlidingPagination $pagination */
            $pagination = $paginator->paginate($qb, $page, self::PER_PAGE);
            $pagination->getRoute();
            $data['pagination'] = $pagination;

            if ($request->isXmlHttpRequest()) {
                $paginationData = $pagination->getPaginationData();

                $data['offlineModels'] = $modelSearchModel->isOffline();

                return new JsonResponse([
                    'html' => $this->renderView(':Search:_search_page.html.twig', $data),
                    'offline' => $modelSearchModel->isOffline(),
                    'page' => $page,
                    'pageCount' => $paginationData['pageCount']
                ]);
            }
        }


        return $this->render('Search/index.html.twig', $data);
    }
}
