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
use AppBundle\Entity\User;
use AppBundle\Exception\ClientNotAgreedToChatException;
use AppBundle\Exception\NotEnoughMoneyException;
use Doctrine\ORM\EntityManager;
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
     * @throws ClientNotAgreedToChatException
     * @throws \ErrorException
     */
    public function addConversationMessage(Conversation $conversation, Message $message)
    {
        if (!$conversation->isRecalculated()) {
            $this->recalculateConversationMessages($conversation);
        }

        $maxFirstMessages = (int) $this->container->getParameter('chat.first_messages_limit') ?: 2;
        $conversationMessagesCount = $this->getConversationPersonalMessagesCount($conversation);

        $securityChecker = $this->container->get('security.authorization_checker');

        // If client has many messages, but did not agreed to pay for chat, rise the exception.
        $shouldPay = $securityChecker->isGranted('ROLE_CLIENT')
                && $conversationMessagesCount >= $maxFirstMessages ? true : false;

        if ($shouldPay && !$conversation->isClientAgreeToPay()) {
            throw new ClientNotAgreedToChatException();
        }

        $em = $this->getManager();

        try {
            $em->beginTransaction();

            $conversation->addMessage($message);
            $message->setConversation($conversation);

            $prevMessage = $this->getConversationLastMessage($conversation, $message);

            if ($prevMessage && !$prevMessage->getFollowingInterval()) {
                $interval = $this->createConversationInterval($conversation, $message, $prevMessage);
                $em->flush();

                // If user does not have enough money and need to pay for message - throw an exception.
                if ($interval) {
                    $user = $this->getUser();

                    if ($shouldPay) {
                        if ($interval->getPrice() > (double)$user->getCoins()) {
                            throw new NotEnoughMoneyException();
                        }

                        $this->payNotPayedConversationIntervals($conversation);

                    // if model sends a message and client agree to pay and has coins - then take the coins.
                    } else if ($securityChecker->isGranted('ROLE_MODEL') && $conversation->isClientAgreeToPay()
                        && (double)$user->getCoins() >= $interval->getPrice()
                    ) {
                        $this->payNotPayedConversationIntervals($conversation);
                    }
                }
            }

            $em->flush();
            $em->commit();

        } catch (\Exception $e) {
            $em->rollBack();
            throw new $e; //\ErrorException("Cannot add new message", 0, 1, __FILE__, __LINE__, $e);
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
        $seconds = 0;
        $price = 0.0;
        $modelEarnings = 0.0;
        foreach ($conversation->getIntervals() as $interval) {
            if ($interval->getStatus() != ConversationInterval::STATUS_PAYED) {
                $this->estimateInterval($interval);
            }
            $seconds += $interval->getSeconds();
            $price += $interval->getPrice();
            $modelEarnings += $interval->getModelEarnings();
        }

        $conversation->setPrice($price);
        $conversation->setSeconds($seconds);
        $conversation->setModelEarnings($modelEarnings);
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

    /**
     * @param User $user
     * @return array
     */
    public function getModelStats(User $user)
    {
        $results = $this->getManager()->createQuery(
            'SELECT SUM(c.modelEarnings) price, SUM(c.seconds) seconds '
            .'FROM AppBundle:User u '
            .'INNER JOIN AppBundle:Conversation c WITH c.model = u '
            .'WHERE u = :user')
            ->setParameter('user', $user)
            ->execute();

        return [
            'total_earnings' => $results[0]['price'],
            'total_seconds' => $results[0]['seconds'],
        ];
    }

    /**
     * @param Conversation $conversation
     * @return \AppBundle\Entity\Message[]
     */
    protected function getNotCalculatedMessages(Conversation $conversation)
    {
        /** @var Message[] $result */
        $result = $this->getManager()->createQuery("SELECT m FROM AppBundle:Message m JOIN m.conversation c "
            . "WHERE (m.author = c.client OR m.author = c.model) AND c = :conversation "
            . "ORDER BY m.dateAdded ASC")
            ->execute(['conversation' => $conversation]);

        return $result;
    }

    /**
     * @param string|null $name
     * @return \Doctrine\Common\Persistence\ObjectManager|EntityManager
     */
    protected function getManager($name = null)
    {
        return $this->container->get('doctrine')->getManager($name);
    }

    /**
     * @param Conversation $conversation
     * @return bool
     */
    protected function recalculateConversationMessages(Conversation $conversation)
    {
        if ($conversation->isRecalculated()) {
            return true;
        }

        $em = $this->getManager();
        $messagesToCalc = $this->getNotCalculatedMessages($conversation);
        $prevMessage = null;

        try {
            $em->beginTransaction();

            foreach ($messagesToCalc as $message) {
                if ($prevMessage && !$message->getPreviousInterval()) {
                    $this->createConversationInterval($conversation, $message, $prevMessage);
                }
                $prevMessage = $message;
            }

            $conversation->setRecalculated(true);
            $em->flush();
            $em->commit();

        } catch (\Exception $e) {
            $em->rollback();
            return false;
        }

        return true;
    }

    /**
     * @param Conversation $conversation
     * @param Message|null $currentMessage
     * @return Message
     */
    protected function getConversationLastMessage(Conversation $conversation, $currentMessage = null)
    {
        $em = $this->getManager();
        $params = ['conversation' => $conversation];

        if ($currentMessage && $currentMessage->getId()) {
            $messageCondition = ' AND m != :current_message ';
            $params['current_message'] = $currentMessage;

        } else {
            $messageCondition = '';
        }

        /** @var Message[] $result */
        $result = $em->createQuery("SELECT m FROM AppBundle:Message m JOIN m.conversation c "
                . "WHERE (m.author = c.client OR m.author = c.model) AND c = :conversation "
                . $messageCondition
                . "ORDER BY m.dateAdded DESC")
            ->setMaxResults(1)
            ->execute($params);

        return $result[0];
    }

    /**
     * @param Conversation $conversation
     * @param Message $message
     * @param Message $prevMessage
     * @return ConversationInterval
     */
    public function createConversationInterval(Conversation $conversation, Message $message, Message $prevMessage)
    {
        $em = $this->getManager();
        $interval = new ConversationInterval($conversation, $prevMessage, $message);
        $em->persist($interval);

        $prevMessage->setFollowingInterval($interval);
        $message->setPreviousInterval($interval);

        $interval->setSeconds($interval->calculateIntervalSeconds());
        $this->estimateInterval($interval);
        $this->estimateConversation($conversation);
        return $interval;
    }

    /**
     * @param Conversation $conversation
     * @return int
     */
    protected function getConversationPersonalMessagesCount(Conversation $conversation)
    {
        $em = $this->getManager();
        $result = $em->createQuery("SELECT COUNT(m) cnt FROM AppBundle:Message m JOIN m.conversation c "
            . "WHERE (m.author = c.client OR m.author = c.model) AND c = :conversation")
            ->setMaxResults(1)
            ->execute(['conversation' => $conversation]);

        return (int) $result[0]['cnt'];
    }

    /**
     * @return mixed|User
     */
    protected function getUser()
    {
        return $this->container->get('security.token_storage')->getToken()->getUser();
    }

    /**
     * @param Conversation $conversation
     */
    private function payNotPayedConversationIntervals(Conversation $conversation)
    {
        $em = $this->getManager();

        $result = $em->createQuery("SELECT ci FROM AppBundle:ConversationInterval ci "
                . "WHERE ci.conversation = :conversation AND ci.status != :status")
            ->execute([
                'conversation' => $conversation,
                'status' => ConversationInterval::STATUS_PAYED
            ]);

        $coinService = $this->container->get('app.coins');
        foreach ($result as $interval) {
            $coinService->payConversationInterval($interval);
        }
    }
}