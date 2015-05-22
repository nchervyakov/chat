<?php
/**
 * Created with IntelliJ IDEA by Nick Chervyakov.
 * User: Nikolay Chervyakov 
 * Date: 22.05.2015
 * Time: 12:35
  */



namespace AppBundle\Controller\Api;


use AppBundle\Entity\User;
use AppBundle\Model\ChatCollection;
use AppBundle\Model\UserCollection;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Request\ParamFetcherInterface;
use Knp\Bundle\PaginatorBundle\Pagination\SlidingPagination;
use Knp\Component\Pager\Event\Subscriber\Paginate\Doctrine\ORM\QuerySubscriber\UsesPaginator;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use FOS\RestBundle\Controller\Annotations as FOSRest;
use JMS\Serializer\Annotation as JMSSerializer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

/**
 * Class MeController
 * @package AppBundle\Controller\Api
 * @FOSRest\NamePrefix("api_v1_")
 * @Security("has_role('ROLE_USER')")
 * @FOSRest\Route("/api/v1")
 */
class MeController extends FOSRestController
{
    /**
     * @FOSRest\Get("/me.{_format}", name="api_v1_get_me", requirements={"_format": "json|xml"})
     * @ApiDoc(
     *      resource=true,
     *      description="Returns current authenticated user",
     *      section="Me",
     *      authentication=true,
     *      authenticationRoles={"ROLE_USER"},
     *      output="AppBundle\Entity\User"
     * )
     *
     * @return User
     */
    public function getMe()
    {
        /** @var User $user */
        $user = $this->getUser();
        $user = $this->getDoctrine()->getRepository('AppBundle:User')->find($user->getId());

        $view = $this->view($user);
        $view->getSerializationContext()->setGroups(['user_read']);

        return $view;
    }

    /**
     * @ApiDoc(
     *      resource=true,
     *      description="Returns current user chats.",
     *      section="Me",
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
    public function getMeChatsAction(ParamFetcherInterface $paramFetcher)
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
        $view->getSerializationContext()
            ->setGroups(['user_read'])
            ->enableMaxDepthChecks();

        return $view;
    }

    /**
     * @ApiDoc(
     *      resource=true,
     *      description="Returns a list of current user companions",
     *      section="Me",
     *      output="AppBundle\Model\UserCollection"
     * )
     *
     * @FOSRest\QueryParam(name="page", requirements="\d+", nullable=true, description="Page from which to list users.")
     * @FOSRest\QueryParam(name="per_page", requirements="\d+", default="10", description="How many users to return per page.")
     *
     * @FOSRest\View()
     * @JMSSerializer\SerializedName("user")
     *
     * @param ParamFetcherInterface $paramFetcher
     * @return \FOS\RestBundle\View\View
     */
    public function getMeCompanionsAction(ParamFetcherInterface $paramFetcher)
    {
        $page = $paramFetcher->get('page');
        $page = null === $page ? 1 : $page;
        $perPage = $paramFetcher->get('per_page');

        /** @var User $user */
        $user = $this->getUser();
        $qb = $this->getDoctrine()->getRepository('AppBundle:User')->prepareUserCompanionsQB($user);

        //$count = $qb->select("COUNT('u')")->getQuery()->getSingleScalarResult();

        $query = $qb->select('u')->getQuery();
        $query->setHint(UsesPaginator::HINT_FETCH_JOIN_COLLECTION, false);

        $paginator = $this->get('knp_paginator');
        /** @var SlidingPagination $pagination */
        $pagination = $paginator->paginate($query, $page, $perPage);
        $paginationData = $pagination->getPaginationData();
        $result = new UserCollection($pagination->getItems(), $page, $perPage);
        $result->setPageCount($paginationData['pageCount']);
        $result->setTotalItemsCount($paginationData['totalCount']);

        $view = $this->view($result);
        $view->getSerializationContext()
            ->setGroups(['user_read'])
            ->enableMaxDepthChecks();

        return $view;
    }

    /**
     * @ApiDoc(
     *      description="Returns model stats collection",
     *      section="Me",
     *      authentication=true,
     *      authenticationRoles={"ROLE_MODEL"}
     * )
     *
     * @Security("has_role('ROLE_MODEL')")
     */
    public function getMeStats()
    {

    }
}