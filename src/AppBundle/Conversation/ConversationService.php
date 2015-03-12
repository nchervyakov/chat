<?php
/**
 * Created by IntelliJ IDEA.
 * User: Nikolay Chervyakov 
 * Date: 10.03.2015
 * Time: 11:26
 */


namespace AppBundle\Conversation;


use AppBundle\Entity\Conversation;
use AppBundle\Entity\ConversationInterval;
use AppBundle\Entity\Message;
use Symfony\Component\DependencyInjection\ContainerAware;

/**
 * Class ConversationService
 * @package AppBundle\Conversation
 */
class ConversationService extends ContainerAware
{
    /**
     * Adds a new message to a conversation and an interval.
     *
     * @param Conversation $conversation
     * @param Message $message
     * @throws \ErrorException
     */
    public function addConversationMessage(Conversation $conversation, Message $message)
    {
        $conn = $this->container->get('doctrine')->getConnection();
        try {
            $conn->beginTransaction();
            $conversation->addMessage($message);
            $message->setConversation($conversation);

            $interval = $this->getActiveInterval($conversation);
            dump($interval->calculateIntervalSeconds());

            $interval->addMessage($message);
            $message->setInterval($interval);
            dump($interval->calculateIntervalSeconds());
            $interval->setSeconds($interval->calculateIntervalSeconds());
            $conn->commit();

        } catch (\Exception $e) {
            $conn->rollBack();
        }
    }

    /**
     * Fetch or create a new active conversation interval.
     *
     * @param Conversation $conversation
     * @return ConversationInterval|mixed|null
     */
    public function getActiveInterval(Conversation $conversation)
    {
        $interval = $conversation->getActiveInterval();
        if (!$interval) {
            $em = $this->container->get('doctrine.orm.entity_manager');
            $interval = new ConversationInterval();
            $em->persist($interval);
            $conversation->addInterval($interval);
            $interval->setConversation($conversation);
        }

        return $interval;
    }
}