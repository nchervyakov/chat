<?php
/**
 * Created with IntelliJ IDEA by Nick Chervyakov.
 * User: Nikolay Chervyakov 
 * Date: 20.05.2015
 * Time: 13:58
  */



namespace AppBundle\Controller\Api;


use AppBundle\Entity\User;
use AppBundle\Model\ChatCollection;
use FOS\RestBundle\Controller\Annotations as FOSRest;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Request\ParamFetcherInterface;
use JMS\Serializer\Annotation as JMSSerializer;
use Knp\Component\Pager\Pagination\SlidingPagination;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

/**
 * Class UserController
 * @package AppBundle\Controller\Api
 * @FOSRest\NamePrefix("api_v1_")
 */
class UserController extends FOSRestController
{
    /**
     * @ApiDoc(
     *      resource=true,
     *      description="Returns a list of users.",
     *      section="Users",
     *      authenticationRoles={"ROLE_ADMIN"}
     * )
     *
     * @Security("has_role('ROLE_ADMIN')")
     *
     * @return \AppBundle\Entity\User[]|array
     */
    public function getUsersAction()
    {
        $repo = $this->getDoctrine()->getRepository('AppBundle:User');
        return $repo->findBy([], ['name' => 'ASC']);
    }

    /**
     * @ApiDoc(
     *      resource=true,
     *      description="Returns a user by ID.",
     *      section="Users",
     *      statusCodes={
     *          200="Returned when successful",
     *          404="Returned when the user is not found."
     *      },
     *      input="AppBundle\Entity\User",
     *      output="AppBundle\Entity\User"
     * )
     *
     * @param $id
     * @return \AppBundle\Entity\User|\Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function getUserAction($id)
    {
        $user = $this->getDoctrine()->getRepository('AppBundle:User')->find($id);

        if (!$user) {
            return $this->createNotFoundException("There is no user with such id.");
        }

        return $user;
    }

    /**
     * @ApiDoc(
     *      resource=true,
     *      description="Create new user",
     *      section="Users"
     * )
     */
    public function postUserAction()
    {

    }

    /**
     * @ApiDoc(
     *      resource=true,
     *      description="Modifies user with new data.",
     *      section="Users"
     * )
     *
     * @param int $id User ID
     */
    public function putUserAction($id)
    {

    }

    /**
     * @ApiDoc(
     *      resource=true,
     *      description="Modifies some properties of the user",
     *      section="Users"
     * )
     *
     * @param int $id User ID
     */
    public function patchUserAction($id)
    {

    }

//    /**
//     * @ApiDoc(
//     *      resource=true,
//     *      description="Deletes the user",
//     *      section="Users",
//     *      authentication=true,
//     *      authenticationRoles={"ROLE_ADMIN"}
//     * )
//     *
//     * @Security("has_role('ROLE_ADMIN')")
//     *
//     * @param int $id User ID
//     */
//    public function deleteUserAction($id)
//    {
//
//    }

    /**
     * @ApiDoc(
     *      resource=true,
     *      description="Returns current user chats.",
     *      section="Users",
     *      output="AppBundle\Model\ChatCollection"
     * )
     *
     * @FOSRest\QueryParam(name="page", requirements="\d+", nullable=true, description="Page from which to list chats.")
     * @FOSRest\QueryParam(name="per_page", requirements="\d+", default="10", description="How many chats to return per page.")
     *
     * @FOSRest\View()
     * @JMSSerializer\SerializedName("chat")
     *
     * @param ParamFetcherInterface $paramFetcher
     * @return ChatCollection
     */
    public function getUserChatsAction(ParamFetcherInterface $paramFetcher)
    {
        $page = $paramFetcher->get('page');
        $page = null === $page ? 1 : $page;
        $perPage = $paramFetcher->get('per_page');

        /** @var User $user */
        $user = $this->getUser();
        $qb = $this->getDoctrine()->getRepository('AppBundle:Conversation')->prepareUserConversationsQB($user);

        $paginator = $this->get('knp_paginator');
        /** @var SlidingPagination $pagination */
        $pagination = $paginator->paginate($qb, $page, $perPage);
        $paginationData = $pagination->getPaginationData();
        $result = new ChatCollection($pagination->getItems(), $page, $perPage);
        $result->setPageCount($paginationData['pageCount']);
        $result->setTotalItemsCount($paginationData['totalCount']);

        $view = $this->view($result);
        $view->getSerializationContext()->setGroups(['user_read']);

        return $view;
    }
}