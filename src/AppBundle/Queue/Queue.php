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
use Doctrine\ORM\EntityManager;
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

        /** @var EntityManager $em */
        $em = $this->container->get('doctrine')->getManager();
        $serializer = $this->container->get('serializer');

        $totalUnreadMessages = $this->container->get('app.conversation')->countUserTotalUnreadMessages($targetUser);

        $qm = new QueueMessage();
        $qm->setTargetUser($targetUser);
        $qm->setName(QueueEvents::MESSAGE_ADDED);
        $qm->setData([
            'message' => json_decode($serializer->serialize($message, 'json'), true),
            'totalUnreadMessages' => $totalUnreadMessages,
            'conversationUnreadMessages' => $conversation->getUserUnseenMessageCount($targetUser),
            'conversationId' => $conversation->getId(),
            'companionId' => $author ? $author->getId() : null,
        ]);

        $em->persist($qm);
        $em->flush();
    }

    public function enqueueMessagesMarkedReadEvent(Conversation $conversation, array $messageIds = [])
    {
        /** @var EntityManager $em */
        $em = $this->container->get('doctrine')->getManager();
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
        $qm->setData([
            'totalUnreadMessages' => $totalUnreadMessages,
            'conversationUnreadMessages' => $conversation->getUserUnseenMessageCount($targetUser),
            'conversationId' => $conversation->getId(),
            'companionId' => $companion ? $companion->getId() : null,
            'messageIds' => $messageIds
        ]);

        $em->persist($qm);
        $em->flush();
    }
}