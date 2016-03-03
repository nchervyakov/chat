<?php
/**
 * Created with IntelliJ IDEA by Nick Chervyakov.
 * User: Nikolay Chervyakov 
 * Date: 27.04.2015
 * Time: 13:42
  */



namespace AppBundle\Queue;


use AppBundle\Entity\Conversation;
use AppBundle\Entity\Message;
use AppBundle\Entity\QueueMessage;
use AppBundle\Entity\User;
use Symfony\Component\DependencyInjection\ContainerAware;

class Queue extends ContainerAware
{
    public function enqueueNewChatMessageEvent(Message $message)
    {
        $author = $message->getAuthor();
        $conversation = $message->getConversation();
        $targetUser = $conversation->getClient() === $author ? $conversation->getModel() : $conversation->getClient();

        if (!$targetUser || !$targetUser->isOnline()) {
            return;
        }

//        /** @var EntityManager $em */
//        $em = $this->container->get('doctrine')->getManager();
        $serializer = $this->container->get('serializer');
        $conversationService = $this->container->get('app.conversation');
        $totalUnreadMessages = $conversationService->countUserTotalUnreadMessages($targetUser);

        $qm = new QueueMessage();
        $qm->setTargetUser($targetUser);
        $qm->setName(QueueEvents::MESSAGE_ADDED);
        $templating = $this->container->get('templating');
        $messageData = [
            'message' => json_decode($serializer->serialize($message, 'json'), true),
            'totalUnreadMessages' => $totalUnreadMessages,
            'conversationUnreadMessages' => $conversation->getUserUnseenMessageCount($targetUser),
            'conversationId' => $conversation->getId(),
            'companionId' => $author ? $author->getId() : null,
            'html' => $templating->render(':Chat:_message.html.twig', ['message' => $message, 'currentUser' => $targetUser])
        ];
        $qm->setData($messageData);

        try {
            $producer = $this->container->get('old_sound_rabbit_mq.notifications_producer');
            $producer->setContentType('application/json');
            $producer->publish(json_encode(['type' => 'new-message', 'data' => $messageData]), 'user.' . $targetUser->getId());
            $producer->publish(json_encode(['type' => 'new-message', 'data' => array_merge($messageData, [
                'conversationUnreadMessages' => $conversation->getUserUnseenMessageCount($author),
                'html' => $templating->render(':Chat:_message.html.twig', ['message' => $message, 'currentUser' => $author]),
                'companionId' => $targetUser->getId(),
                'totalUnreadMessages' => $conversationService->countUserTotalUnreadMessages($author),
            ])]), 'user.' . $author->getId());

        } catch (\ErrorException $e){
            $this->container->get('logger')->addCritical($e->getMessage());
        }

//        $em->persist($qm);
//        $em->flush();
    }

    public function enqueueMessagesMarkedReadEvent(Conversation $conversation, array $messageIds = [])
    {
//        /** @var EntityManager $em */
//        $em = $this->container->get('doctrine')->getManager();
        /** @var User $user */
        $user = $this->container->get('security.token_storage')->getToken()->getUser();
        $targetUser = $user;
        $companion = $conversation->getClient()->getId() == $user->getId() ? $conversation->getModel() : $conversation->getClient();

        if (!$targetUser || !$targetUser->isOnline()) {
            return;
        }

        $totalUnreadMessages = $this->container->get('app.conversation')->countUserTotalUnreadMessages($targetUser);

        $qm = new QueueMessage();
        $qm->setTargetUser($targetUser);
        $qm->setName(QueueEvents::MESSAGE_READ);
        $messageData = [
            'totalUnreadMessages' => $totalUnreadMessages,
            'conversationUnreadMessages' => $conversation->getUserUnseenMessageCount($targetUser),
            'conversationId' => $conversation->getId(),
            'companionId' => $companion ? $companion->getId() : null,
            'messageIds' => $messageIds
        ];
        $qm->setData($messageData);

        try {
            $producer = $this->container->get('old_sound_rabbit_mq.notifications_producer');
            $producer->setContentType('application/json');
            $producer->publish(json_encode(['type' => 'messages-marked-read', 'data' => $messageData]), 'user.' . $targetUser->getId());

        } catch (\ErrorException $e){
            $this->container->get('logger')->addCritical($e->getMessage());
        }

//        $em->persist($qm);
//        $em->flush();
    }
}