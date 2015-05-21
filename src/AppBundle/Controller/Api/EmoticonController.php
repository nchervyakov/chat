<?php
/**
 * Created with IntelliJ IDEA by Nick Chervyakov.
 * User: Nikolay Chervyakov 
 * Date: 20.05.2015
 * Time: 14:05
  */



namespace AppBundle\Controller\Api;


use AppBundle\Entity\Emoticon;
use AppBundle\Model\EmoticonCollection;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as FOSRest;
use FOS\RestBundle\Request\ParamFetcherInterface;
use Knp\Bundle\PaginatorBundle\Pagination\SlidingPagination;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

/**
 * Class EmoticonController
 * @package AppBundle\Controller\Api
 *
 * @FOSRest\NamePrefix("api_v1_")
 * @FOSRest\View(templateVar="emoticon")
 * @Security("has_role('ROLE_USER')")
 */
class EmoticonController extends FOSRestController
{
    const PER_PAGE = 10;

    /**
     * @ApiDoc(
     *      description="Returns a collection of emoticons.",
     *      statusCodes={
     *          200="Returned when successful"
     *      },
     *      section="Emoticons",
     *      output="AppBundle\Model\EmoticonCollection"
     * )
     *
     * @FOSRest\QueryParam(name="page", requirements="\d+", nullable=true, description="Page from which to list emoticons.")
     * @FOSRest\QueryParam(name="per_page", requirements="\d+", default="10", description="How many emoticons to return per page.")
     *
     * @FOSRest\View()
     *
     * Cache(expires="+1 second", public=true, vary={"Content-Type"})
     * @param ParamFetcherInterface $paramFetcher
     * @return \AppBundle\Entity\Emoticon[]|array
     */
    public function getEmoticonsAction(ParamFetcherInterface $paramFetcher)
    {
        $page = $paramFetcher->get('page');
        $page = null === $page ? 1 : $page;
        $perPage = $paramFetcher->get('per_page');

        $qb = $this->getDoctrine()->getRepository('AppBundle:Emoticon')->createQueryBuilder('e');

        $paginator = $this->get('knp_paginator');
        /** @var SlidingPagination $pagination */
        $pagination = $paginator->paginate($qb, $page, $perPage);
        $paginationData = $pagination->getPaginationData();
        $result = new EmoticonCollection($pagination->getItems(), $page, $perPage);
        $result->setPageCount($paginationData['pageCount']);

        return $result;
    }

    /**
     * @ApiDoc(
     *      resource=true,
     *      description="Returns an emoticon.",
     *      section="Emoticons",
     *      statusCodes={
     *          200="Returned when successful",
     *          404="Returned when the emoticon is not found."
     *      },
     *      output="AppBundle\Entity\Emoticon"
     * )
     *
     * Cache(
     *      lastModified="emoticon.getDateUpdated()",
     *      etag="'Emoticon_' ~ emoticon.getId() ~ ' ' ~ emoticon.getDateUpdated()",
     *      public=true,
     *      vary={"Content-Type"}
     * )
     *
     * @FOSRest\View()
     * @param int $id Emoticon id
     * @return Emoticon
     */
    public function getEmoticonAction($id)
    {
        $emoticon = $this->getDoctrine()->getRepository('AppBundle:Emoticon')->find($id);

        if (!$emoticon) {
            return $this->createNotFoundException("There is no emoticon with such id.");
        }

        return $emoticon;
    }

//    /**
//     * @ApiDoc(
//     *      resource=true,
//     *      description="Creates new emoticon. Available only to admin.",
//     *      section="Emoticons",
//     *      authentication=true,
//     *      authenticationRoles={"ROLE_ADMIN"}
//     * )
//     * @Security("has_role('ROLE_ADMIN')")
//     */
//    public function postEmoticonAction()
//    {
//        throw $this->createAccessDeniedException();
//    }
//
//    /**
//     * @ApiDoc(
//     *      resource=true,
//     *      description="Modifies a group by ID.",
//     *      section="Emoticons",
//     *      input="AppBundle\Entity\Emoticon",
//     *      authentication=true,
//     *      authenticationRoles={"ROLE_ADMIN"}
//     * )
//     * @Security("has_role('ROLE_ADMIN')")
//     * @param int $id Emoticon ID
//     */
//    public function putEmoticonAction($id)
//    {
//        throw $this->createAccessDeniedException();
//    }
//
//    /**
//     * @ApiDoc(
//     *      resource=true,
//     *      description="Modifies a certain properties of an emoticon by ID. Available only to admin.",
//     *      section="Emoticons",
//     *      authentication=true,
//     *      authenticationRoles={"ROLE_ADMIN"}
//     * )
//     *
//     * @Security("has_role('ROLE_ADMIN')")
//     * @param int $id Emoticon ID
//     */
//    public function patchEmoticonAction($id)
//    {
//        throw $this->createAccessDeniedException();
//    }
//
//    /**
//     ** @ApiDoc(
//     *      resource=true,
//     *      description="Deletes an emoticon.",
//     *      section="Emoticons",
//     *      authentication=true,
//     *      authenticationRoles={"ROLE_ADMIN"}
//     * )
//     *
//     * @Security("has_role('ROLE_ADMIN')")
//     * @param int $id Emoticon ID
//     */
//    public function deleteEmoticonAction($id)
//    {
//        throw $this->createAccessDeniedException();
//    }
}