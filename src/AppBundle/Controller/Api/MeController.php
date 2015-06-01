<?php
/**
 * Created with IntelliJ IDEA by Nick Chervyakov.
 * User: Nikolay Chervyakov 
 * Date: 22.05.2015
 * Time: 12:35
  */



namespace AppBundle\Controller\Api;


use AppBundle\Entity\Conversation;
use AppBundle\Entity\User;
use AppBundle\Model\ChatCollection;
use AppBundle\Model\ModelStatCollection;
use AppBundle\Model\UserCollection;
use Doctrine\ORM\EntityManager;
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
 *
 * @FOSRest\NamePrefix("api_v1_")
 * @FOSRest\Route("/api/v1")
 *
 * @Security("has_role('ROLE_USER')")
 */
class MeController extends FOSRestController
{
    /**
     * @ApiDoc(
     *      resource=true,
     *      description="Returns current authenticated user",
     *      section="Me",
     *      authentication=true,
     *      authenticationRoles={"ROLE_USER"},
     *      output="AppBundle\Entity\User"
     * )
     *
     * @FOSRest\Get("/me.{_format}", name="api_v1_get_me", requirements={"_format": "json|xml"}, defaults={"_format": "json"})
     *
     * @return \FOS\RestBundle\View\View
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
     * @FOSRest\View(serializerEnableMaxDepthChecks=true)
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
            ->setGroups(['chat_list', 'essential_public'])
            ->enableMaxDepthChecks();

        return $view;
    }

    /**
     * @ApiDoc(
     *      resource=true,
     *      description="Returns current user chats.",
     *      section="Me",
     *      output="AppBundle\Entity\Conversation"
     * )
     *
     * @FOSRest\View(serializerEnableMaxDepthChecks=true)
     * @FOSRest\Get("/me/chats/{companionId}", name="api_v1_get_me_chat_with_companion", requirements={"_format": "json|xml"}, defaults={"_format": "json"})
     *
     * @param $companionId
     * @return \FOS\RestBundle\View\View
     */
    public function getMeChatAction($companionId)
    {
        /** @var User $user */
        $user = $this->getUser();

        $companion = $this->getDoctrine()->getRepository('AppBundle:User')->find($companionId);

        if (!$companion) {
            throw $this->createNotFoundException('Companion not found');
        }

        $conversation = $this->getDoctrine()->getRepository('AppBundle:Conversation')->getByUsers($user, $companion);
        $accessEvaluator = $this->get('app.request_access_evaluator');

        if (!$conversation || !$accessEvaluator->canChatWith($conversation->getCompanion($user))) {
            throw $this->createNotFoundException('There is no chat with such ID.');
        }

        $view = $this->view($conversation);
        $view->getSerializationContext()->setGroups(['user_read'])
            ->enableMaxDepthChecks();

        return $view;
    }

    /**
     * @ApiDoc(
     *      resource=true,
     *      description="Creates a new chat for current user",
     *      section="Me",
     *      authentication=true,
     *      authenticationRoles={"ROLE_USER"},
     *      output={
     *          "class"="AppBundle\Entity\Conversation",
     *          "groups"={"user_read"}
     *      }
     * )
     *
     * @FOSRest\Post("/me/chats/{companionId}", name="api_v1_post_me_chat", requirements={"_format": "json|xml"}, defaults={"_format": "json"})
     * @FOSRest\View()
     *
     * @param $companionId
     * @return \FOS\RestBundle\View\View
     */
    public function postMeChatAction($companionId)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        /** @var User $user */
        $user = $this->getUser();

        $companion = $this->getDoctrine()->getRepository('AppBundle:User')->find($companionId);

        if (!$companion) {
            throw $this->createNotFoundException('Companion not found');
        }

        if (!$this->get('app.request_access_evaluator')->canChatWith($companion)) {
            throw $this->createAccessDeniedException("You cannot chat with this user.");
        }

        $conversation = $em->getRepository('AppBundle:Conversation')->getByUsers($user, $companion);
        if ($conversation) {
            throw $this->createAccessDeniedException("The conversation already exists.");
        }

        $conversation = $em->getRepository('AppBundle:Conversation')->createByUsers($user, $companion);

        $em->persist($conversation);
        $em->flush();

        $view = $this->view($conversation);
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
     *      authenticationRoles={"ROLE_MODEL"},
     *      output="AppBundle\Model\ModelStatCollection"
     * )
     *
     * @FOSRest\QueryParam(name="page", requirements="\d+", nullable=true, description="Page from which to list stats.")
     * @FOSRest\QueryParam(name="per_page", requirements="\d+", default="10", description="How many stat items to return per page.")
     *
     * @Security("has_role('ROLE_MODEL')")
     *
     * @param ParamFetcherInterface $paramFetcher
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getMeStatsAction(ParamFetcherInterface $paramFetcher)
    {
        $page = $paramFetcher->get('page');
        $page = null === $page ? 1 : $page;
        $perPage = $paramFetcher->get('per_page');

        /** @var User $user */
        $user = $this->getUser();

        $userRepo = $this->getDoctrine()->getRepository('AppBundle:User');
        $conversationsQB = $userRepo->createUserConversationsQueryBuilder($user);

        $paginator = $this->get('knp_paginator');
        /** @var SlidingPagination $pagination */
        $pagination = $paginator->paginate($conversationsQB, $page, $perPage);

        $convService = $this->get('app.conversation');
        foreach ($pagination as $conversation) {
            $convService->estimateConversation($conversation);
        }
        $this->getDoctrine()->getManager()->flush();
        $stats = $convService->getModelStats($user);
                     //dump($pagination->getItems());
        $paginationData = $pagination->getPaginationData();
        $result = new ModelStatCollection($pagination->getItems(), $page, $perPage);
        $result->setPageCount($paginationData['pageCount']);
        $result->setTotalItemsCount($paginationData['totalCount']);
        $result->setTotalEarnings($stats['total_earnings']);
        $result->setTotalSeconds($stats['total_seconds']);

        $view = $this->view($result);
        $view->getSerializationContext()
            ->setGroups(['model_read', 'model_stat'])
            ->enableMaxDepthChecks();

        return $view;
    }
}