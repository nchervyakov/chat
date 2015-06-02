<?php
/**
 * Created with IntelliJ IDEA by Nick Chervyakov.
 * User: Nikolay Chervyakov 
 * Date: 20.05.2015
 * Time: 14:00
  */



namespace AppBundle\Controller\Api;


use AppBundle\Entity\ImageMessage;
use AppBundle\Entity\Message;
use AppBundle\Entity\User;
use AppBundle\Model\MessageCollection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query;
use FOS\RestBundle\Controller\Annotations as FOSRest;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Request\ParamFetcherInterface;
use Knp\Component\Pager\Pagination\SlidingPagination;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class MessageController
 * @package AppBundle\Controller\Api
 * @FOSRest\NamePrefix("api_v1_")
 *
 * @Security("has_role('ROLE_USER')")
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
     *      authentication=true,
     *      output={
     *          "class"="AppBundle\Entity\Message",
     *          "groups"={"user_read"}
     *      }
     * )
     *
     * @FOSRest\View()
     *
     * @param $chatId
     * @param int $id Message ID
     * @return \FOS\RestBundle\View\View
     */
    public function getMessageAction($chatId, $id)
    {
        $conversation = $this->getConversationForProcess($chatId);

        $qb = $this->getDoctrine()->getRepository('AppBundle:Message')->createQueryBuilder('m');
        $qb->where('m.conversation = :conversation AND m.id = :id')
            ->setParameters(['conversation' => $conversation, 'id' => $id]);

        $result = $qb->getQuery()->getSingleResult();

        $view = $this->view($result);
        $view->getSerializationContext()
            ->enableMaxDepthChecks()
            ->setGroups(['user_read']);

        return $view;
    }

    /**
     * @ApiDoc(
     *      resource=true,
     *      description="Creates a new message in the chat",
     *      section="Chats",
     *      authentication=true,
     *      authenticationRoles={"ROLE_MODEL", "ROLE_CLIENT"},
     *      parameters={
     *          {"name"="discriminator", "dataType"="string", "required"=true, "format"="{'text', 'image'}"},
     *          {"name"="image[file]", "dataType"="file", "required"=true, "description"="Image file in case of image message"},
     *          {"name"="content", "dataType"="string", "required"=true, "description"="Content or text messages"}
     *      },
     *      output={
     *          "class"="AppBundle\Entity\Message",
     *          "groups"={"user_read"}
     *      }
     * )
     *
     * @FOSRest\View()
     *
     * @param Request $request
     * @param int $chatId Chat ID
     * @return \FOS\RestBundle\View\View
     */
    public function postMessageAction(Request $request, $chatId)
    {
        /** @var User $user */
        $user = $this->getUser();
        $conversation = $this->getConversationForProcess($chatId);

        $form = $this->get('form.factory')->createNamed('', 'message', null, [
            'method' => 'POST',
            'validation_groups' => ['create'],
            'api' => true,
            'allow_extra_fields' => true
        ]);

        $form->handleRequest($request);

        if ($form->isValid()) {
            /** @var Message $message */
            $message = $form->getData();
            $message->setAuthor($user);

            /** @var EntityManager $em */
            $em = $this->getDoctrine()->getManager();

            $em->persist($message);
            $this->get('app.conversation')->addConversationMessage($conversation, $message);
            $em->flush();

            if ($message instanceof ImageMessage) {
                /** @var ImageMessage $message */
                $this->get('app.image')->fixOrientation($message->getImageFile());
            }

            $companion = $conversation->getCompanion($user);
            if ($companion->isOnline()) {
                $this->get('app.mq_notificator')->notifyConversationStatsChanged($conversation, $companion);
            }

            $view = $this->view($message);
            $view->setStatusCode(201);
            $view->getSerializationContext()
                ->setGroups(['user_read'])
                ->enableMaxDepthChecks();

            return $view;
        }

        $view = $this->view($form);
        $view->setStatusCode(400);
        return $view;
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

//    /**
//     * @ApiDoc(
//     *      resource=true,
//     *      description="Modifies some properties of the message in the chat",
//     *      section="Chats"
//     * )
//     *
//     * @param $chatId
//     * @param int $id Message ID
//     */
//    public function patchMessageAction($chatId, $id)
//    {
//
//    }

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