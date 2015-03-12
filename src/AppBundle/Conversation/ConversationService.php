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
            $interval->addMessage($message);
            $message->setInterval($interval);
            $interval->setSeconds($interval->calculateIntervalSeconds());
            $this->estimateInterval($interval);

            $conn->commit();

        } catch (\Exception $e) {
            $conn->rollBack();
            throw new \ErrorException("Cannot add new message", 0, 1, __FILE__, __LINE__, $e);
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
            $this->estimateInterval($interval);
        }

        return $interval;
    }

    /**
     * @param Conversation $conversation
     */
    public function estimateConversation(Conversation $conversation)
    {
        foreach ($conversation->getIntervals() as $interval) {
            $this->estimateInterval($interval);
        }
    }

    /**
     * @param ConversationInterval $interval
     * @throws \ErrorException
     */
    public function estimateInterval(ConversationInterval $interval)
    {
        $rate = (float) $this->container->getParameter('payment.minute_rate');
        $modelShare = (float) $this->container->getParameter('payment.model_share');

        if ($interval->getStatus() != ConversationInterval::STATUS_PAYED) {
            $interval->setPrice($interval->getSeconds() / 60 * $rate);
            $interval->setMinuteRate($rate);
            $interval->setModelShare($modelShare);
            $interval->setModelEarnings($interval->getPrice() * $modelShare);
        }
    }

    /**
     * @param Conversation[] $conversations
     */
    public function estimateConversations($conversations)
    {
        foreach ($conversations as $conversation) {
            $this->estimateConversation($conversation);
        }
    }
}