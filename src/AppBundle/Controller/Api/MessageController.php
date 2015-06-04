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
use AppBundle\Entity\ParticipantMessage;
use AppBundle\Entity\User;
use AppBundle\Model\MessageCollection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query;
use FOS\RestBundle\Controller\Annotations as FOSRest;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Request\ParamFetcherInterface;
use Knp\Component\Pager\Pagination\SlidingPagination;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Class MessageController
 * @package AppBundle\Controller\Api
 * @Route("/api/v1")
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
     *      authentication=true,
     *      output={
     *          "class"="AppBundle\Model\MessageCollection",
     *          "groups"={"user_read"}
     *      }
     * )
     *
     * @FOSRest\QueryParam(name="page", requirements="\d+", nullable=true, description="Page from which to list messages.")
     * @FOSRest\QueryParam(name="per_page", requirements="\d+", default="10", description="How many messages to return per page.")
     * @FOSRest\QueryParam(name="before_message_id", requirements="|\d+", default="", description="Top exclusive limit of message ids to search.")
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
        $beforeMessageId = $paramFetcher->get('before_message_id');

        $qb = $this->getDoctrine()->getRepository('AppBundle:Message')->createQueryBuilder('m');
        $qb->where('m.conversation = :conversation')
            ->setParameter('conversation', $conversation);
        if ($beforeMessageId) {
            $qb->andWhere('m.id < :before_message_id')->setParameter('before_message_id', $beforeMessageId);
        }

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
        $message = $this->getConversationMessageForProcess($chatId, $id);

        $view = $this->view($message);
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

    /**
     * @ApiDoc(
     *      resource=true,
     *      description="Modifies the message",
     *      section="Chats",
     *      authentication=true,
     *      authenticationRoles={"ROLE_USER"},
     *      parameters={
     *          {"name"="deleted_by_user", "dataType"="boolean", "required"=false, "description"="Allows to mark message as deleted, but not reverse."},
     *          {"name"="seen_by_client", "dataType"="boolean", "required"=false, "description"="Marks message as seen by the client."},
     *          {"name"="seen_by_model", "dataType"="boolean", "required"=false, "description"="Marks message as seen by the client."},
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
     * @param $chatId
     * @param int $id Message ID
     * @return \FOS\RestBundle\View\View
     */
    public function patchMessageAction(Request $request, $chatId, $id)
    {
        $message = $this->getConversationMessageForProcess($chatId, $id);

        $seenByClient = $message->isSeenByClient();
        $seenByModel = $message->isSeenByClient();

        $form = $this->get('form.factory')->createNamed('', 'message', $message, [
            'method' => 'PATCH',
            'validation_groups' => ['update'],
            'api' => true,
            'patch' => true,
            'allow_extra_fields' => true
        ]);

        $form->submit($request->request->all(), false);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->flush();

            if ($seenByClient != $message->isSeenByClient() || $seenByModel != $message->isSeenByModel()) {
                $conversation = $message->getConversation();
                /** @var User $user */
                $user = $this->getUser();
                $messageIds = [$message->getId()];

                $this->get('app.conversation')->markConversationMessagesSeenById($conversation, $user, $messageIds);
                $this->get('app.conversation')->calculateWhoSeen($conversation);
                $this->get('app.queue')->enqueueMessagesMarkedReadEvent($conversation, $messageIds);
            }

            $view = $this->view($message);
            $view->getSerializationContext()
                ->setGroups(['user_read'])
                ->enableMaxDepthChecks();

            return $view;
        }

        $view = $this->view($form);
        $view->setStatusCode(400);
        return $view;
    }

    /**
     * @ApiDoc(
     *      resource=true,
     *      description="Creates a message complaint",
     *      section="Chats",
     *      authentication=true,
     *      authenticationRoles={"ROLE_MODEL"},
     *      output={
     *          "class"="AppBundle\Entity\MessageComplaint",
     *          "groups"={"user_read"}
     *      }
     * )
     *
     * @Security("has_role('ROLE_MODEL')")
     *
     * @FOSRest\Post("/chats/{chatId}/messages/{messageId}/complaint", name="api_v1_post_chat_message_complaint", requirements={"_format": "json|xml"}, defaults={"_format": "json"})     *
     *
     * @param $chatId
     * @param int $messageId Message ID
     * @return \FOS\RestBundle\View\View
     */
    public function postMessageComplaintAction($chatId, $messageId)
    {
        $message = $this->getConversationMessageForProcess($chatId, $messageId);

        if ($message->getComplaint()) {
            throw new BadRequestHttpException("THe message is complained already.");
        }

        if (!$message instanceof ParticipantMessage) {
            throw new BadRequestHttpException("You cannot complain system messages");
        }

        /** @var User $user */
        $user = $this->getUser();

        if ($message->getAuthor()->getId() === $user->getId()) {
            throw $this->createAccessDeniedException("You cannot complain your own message.");
        }

        $this->container->get('app.conversation')->complainMessage($message);

        $view = $this->view($message->getComplaint());
        $view->setStatusCode(201);
        $view->getSerializationContext()
            ->enableMaxDepthChecks()
            ->setGroups(['user_read']);

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
     *      description="Returns message complaint instance",
     *      section="Chats",
     *      authentication=true,
     *      authenticationRoles={"ROLE_USER"}
     * )
     *
     * @param int $chatId Chat ID
     * @param int $messageId Message ID
     * @return \FOS\RestBundle\View\View
     */
    public function getMessageComplaintAction($chatId, $messageId)
    {
        $conversation = $this->getConversationForProcess($chatId);

        $qb = $this->getDoctrine()->getRepository('AppBundle:Message')->createQueryBuilder('m');
        $qb->where('m.conversation = :conversation AND m.id = :id')
            ->setParameters(['conversation' => $conversation, 'id' => $messageId]);

        /** @var Message|null $message */
        $message = $qb->getQuery()->setMaxResults(1)->getOneOrNullResult();

        if (!$message) {
            throw $this->createNotFoundException("No such message");
        }

        if (!$message->getComplaint()) {
            throw $this->createNotFoundException("The message does not have a complaint.");
        }

        $view = $this->view($message->getComplaint());
        $view->getSerializationContext()
            ->enableMaxDepthChecks()
            ->setGroups(['user_read']);

        return $view;
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

    /**
     * @param int $chatId
     * @param int $id Message ID
     * @return Message
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    protected function getConversationMessageForProcess($chatId, $id)
    {
        $conversation = $this->getConversationForProcess($chatId);

        $qb = $this->getDoctrine()->getRepository('AppBundle:Message')->createQueryBuilder('m');
        $qb->where('m.conversation = :conversation AND m.id = :id')
            ->setParameters(['conversation' => $conversation, 'id' => $id]);

        $message = $qb->getQuery()->setMaxResults(1)->getOneOrNullResult();

        if (!$message) {
            throw $this->createNotFoundException("No such message");
        }

        return $message;
    }
}