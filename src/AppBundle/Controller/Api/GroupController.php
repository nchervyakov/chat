<?php
/**
 * Created with IntelliJ IDEA by Nick Chervyakov.
 * User: Nikolay Chervyakov 
 * Date: 20.05.2015
 * Time: 14:05
  */



namespace AppBundle\Controller\Api;


use AppBundle\Model\GroupCollection;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as FOSRest;
use FOS\RestBundle\Request\ParamFetcherInterface;
use Knp\Component\Pager\Pagination\SlidingPagination;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

/**
 * Class GroupController
 * @package AppBundle\Controller\Api
 * @FOSRest\NamePrefix("api_v1_")
 */
class GroupController extends FOSRestController
{
    /**
     * @ApiDoc(
     *      resource=true,
     *      description="Returns a collection of all groups",
     *      section="Groups",
     *      output="AppBundle\Model\GroupCollection"
     * )
     *
     * @FOSRest\QueryParam(name="page", requirements="\d+", nullable=true, description="Page from which to list groups.")
     * @FOSRest\QueryParam(name="per_page", requirements="\d+", default="10", description="How many groups to return per page.")
     *
     * @FOSRest\View()
     *
     * @param ParamFetcherInterface $paramFetcher
     * @return \AppBundle\Entity\Group[]|array
     */
    public function getGroupsAction(ParamFetcherInterface $paramFetcher)
    {
        $page = $paramFetcher->get('page');
        $page = null === $page ? 1 : $page;
        $perPage = $paramFetcher->get('per_page');

        $qb = $this->getDoctrine()->getRepository('AppBundle:Group')->createQueryBuilder('g');
        $qb->orderBy('g.name', 'ASC');

        $paginator = $this->get('knp_paginator');
        /** @var SlidingPagination $pagination */
        $pagination = $paginator->paginate($qb, $page, $perPage);
        $paginationData = $pagination->getPaginationData();
        $result = new GroupCollection($pagination->getItems(), $page, $perPage);
        $result->setPageCount($paginationData['pageCount']);

        return $result;
    }

    /**
     * @ApiDoc(
     *      resource=true,
     *      description="Returns a group by ID",
     *      section="Groups",
     *      output="AppBundle\Entity\Group"
     * )
     *
     * @FOSRest\View()
     *
     * @param int $id Group ID
     * @return \AppBundle\Entity\Group
     */
    public function getGroupAction($id)
    {
        $group = $this->getDoctrine()->getRepository('AppBundle:Group')->find($id);

        if (!$group) {
            throw $this->createNotFoundException("Invalid id for group.");
        }

        return $this->view($group);
    }

//    /**
//     * @ApiDoc(
//     *      resource=true,
//     *      description="Creates new group",
//     *      section="Groups",
//     *      input="AppBundle\Entity\Group",
//     *      output="AppBundle\Entity\Group",
//     *      authenticationRoles={"ROLE_ADMIN"}
//     * )
//     * @Security("has_role('ROLE_ADMIN')")
//     */
//    public function postGroupAction()
//    {
//        throw $this->createAccessDeniedException();
//    }
//
//    /**
//     * @ApiDoc(
//     *      resource=true,
//     *      description="Modifies a group by ID",
//     *      section="Groups",
//     *      authenticationRoles={"ROLE_ADMIN"}
//     * )
//     * @Security("has_role('ROLE_ADMIN')")
//     * @param int $id Group ID
//     */
//    public function putGroupAction($id)
//    {
//        throw $this->createAccessDeniedException();
//    }
//
//    /**
//     * @ApiDoc(
//     *      resource=true,
//     *      description="Modifies a certain properties of a group by ID",
//     *      section="Groups",
//     *      authenticationRoles={"ROLE_ADMIN"}
//     * )
//     * @Security("has_role('ROLE_ADMIN')")
//     * @param int $id Group ID
//     */
//    public function patchGroupAction($id)
//    {
//        throw $this->createAccessDeniedException();
//    }
//
//    /**
//     * @ApiDoc(
//     *      resource=true,
//     *      description="Deletes a group by ID",
//     *      section="Groups",
//     *      authenticationRoles={"ROLE_ADMIN"}
//     * )
//     *
//     * @Security("has_role('ROLE_ADMIN')")
//     * @param int $id Group ID
//     */
//    public function deleteGroupAction($id)
//    {
//        throw $this->createAccessDeniedException();
//    }
}