<?php
/**
 * Created with IntelliJ IDEA by Nick Chervyakov.
 * User: Nikolay Chervyakov 
 * Date: 20.05.2015
 * Time: 14:00
  */



namespace AppBundle\Controller\Api;


use AppBundle\Entity\User;
use AppBundle\Model\MessageCollection;
use FOS\RestBundle\Controller\Annotations as FOSRest;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Request\ParamFetcherInterface;
use Knp\Component\Pager\Pagination\SlidingPagination;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

/**
 * Class MessageController
 * @package AppBundle\Controller\Api
 * @FOSRest\NamePrefix("api_v1_")
 */
class MessageController extends FOSRestController
{
    /**
     * @ApiDoc(
     *      resource=true,
     *      description="Returns a chat messages",
     *      section="Chats",
     *      authenticationRoles={"ROLE_USER"},
     *      authentication=true
     * )
     *
     * @FOSRest\QueryParam(name="page", requirements="\d+", nullable=true, description="Page from which to list messages.")
     * @FOSRest\QueryParam(name="per_page", requirements="\d+", default="10", description="How many messages to return per page.")
     *
     * @FOSRest\View(serializerEnableMaxDepthChecks=true, serializerGroups={"user_read"})
     *
     * @param int $chatId Chat ID
     * @param ParamFetcherInterface $paramFetcher
     * @return \FOS\RestBundle\View\View
     */
    public function getMessagesAction(ParamFetcherInterface $paramFetcher, $chatId)
    {
        $conversation = $this->getConversationForProcess($chatId);

        $page = $paramFetcher->get('page');
        $page = null === $page ? 1 : $page;
        $perPage = $paramFetcher->get('per_page');

        $qb = $this->getDoctrine()->getRepository('AppBundle:Message')->createQueryBuilder('m');
        $qb->where('m.conversation = :conversation')
            ->setParameter('conversation', $conversation);
        $qb->orderBy('m.dateAdded', 'DESC');

        $paginator = $this->get('knp_paginator');
        /** @var SlidingPagination $pagination */
        $pagination = $paginator->paginate($qb, $page, $perPage);
        $paginationData = $pagination->getPaginationData();
        $result = new MessageCollection($pagination->getItems(), $page, $perPage);
        $result->setPageCount($paginationData['pageCount']);
        $result->setTotalItemsCount($paginationData['totalCount']);

        $view = $this->view($result);
        $view->getSerializationContext()->setGroups(['message_list', 'essential_public'])
            ->enableMaxDepthChecks();
        return $view;
    }

    /**
     * @ApiDoc(
     *      resource=true,
     *      description="Returns a chat message collection",
     *      section="Chats",
     *      authenticationRoles={"ROLE_USER"},
     *      authentication=true
     * )
     *
     * @param $chatId
     * @param int $id Message ID
     */
    public function getMessageAction($chatId, $id)
    {

    }

    /**
     * @ApiDoc(
     *      resource=true,
     *      description="Creates a new message in the chat",
     *      section="Chats"
     * )
     * @param int $chatId Chat ID
     */
    public function postMessageAction($chatId)
    {

    }

//    /**
//     * @ApiDoc(
//     *      resource=true,
//     *      description="Modifies the message in the chat",
//     *      section="Chats"
//     * )
//     *
//     * @param $chatId
//     * @param int $id Message ID
//     */
//    public function putMessageAction($chatId, $id)
//    {
//
//    }

    /**
     * @ApiDoc(
     *      resource=true,
     *      description="Modifies some properties of the message in the chat",
     *      section="Chats"
     * )
     *
     * @param $chatId
     * @param int $id Message ID
     */
    public function patchMessageAction($chatId, $id)
    {

    }

    /**
     * @ApiDoc(
     *      resource=true,
     *      description="Marks the message as deleted by the author",
     *      section="Chats"
     * )
     *
     * @param $chatId
     * @param int $id Message ID
     */
    public function patchMessageDeleteAction($chatId, $id)
    {

    }

    /**
     * @ApiDoc(
     *      resource=true,
     *      description="Creates a message complaint",
     *      section="Chats",
     *      authentication=true,
     *      authenticationRoles={"ROLE_MODEL"}
     * )
     *
     * @Security("has_role('ROLE_MODEL')")
     *
     * @param $chatId
     * @param int $id Message ID
     */
    public function postMessageComplaintAction($chatId, $id)
    {

    }

    /**
     * @ApiDoc(
     *      resource=true,
     *      description="Returns message complaint instance",
     *      section="Chats"
     * )
     *
     * @param int $chatId Chat ID
     * @param int $messageId Message ID
     */
    public function getMessageComplaintAction($chatId, $messageId)
    {

    }

    /**
     * @param $conversationId
     * @return \AppBundle\Entity\Conversation
     */
    protected function getConversationForProcess($conversationId)
    {
        /** @var User $user */
        $user = $this->getUser();

        $conversation = $this->getDoctrine()->getRepository('AppBundle:Conversation')->find($conversationId);

        if (!$conversation || (!$conversation->isParticipant($user) && !$this->isGranted('ROLE_ADMIN'))) {
            throw $this->createNotFoundException('Chat with id "' . $conversationId . '" is not found.');
        }

        return $conversation;
    }
}