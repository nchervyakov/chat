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
use AppBundle\Entity\MessageComplaint;
use AppBundle\Entity\MessageRepository;
use AppBundle\Entity\ParticipantMessage;
use AppBundle\Entity\User;
use AppBundle\Exception\ClientNotAgreedToChatException;
use AppBundle\Exception\NotEnoughMoneyException;
use Doctrine\DBAL\ConnectionException;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

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
     * @throws \Exception
     * @trows NotEnoughMoneyException
     */
    public function addConversationMessage(Conversation $conversation, Message $message)
    {
        // Recalculate old format intervals
        if (!$conversation->isRecalculated()) {
            $this->recalculateConversationMessages($conversation);
        }

        $shouldPay = $this->checkCurrentUserShouldPay($conversation);

        if ($shouldPay && !$conversation->isClientAgreeToPay()) {
            throw new ClientNotAgreedToChatException();
        }

        $em = $this->getManager();

        try {
            $em->beginTransaction();

            $conversation->addMessage($message);
            $message->setConversation($conversation);
            $message->setSeenByAuthor();

            $prevMessage = $this->getConversationLastMessage($conversation, $message);

            if ($prevMessage/* && !$prevMessage->getFollowingInterval()*/) {
                $conversation->setStalePaymentInfo(true);
                $interval = $this->createConversationInterval($conversation, $message, $prevMessage);
                if (!$shouldPay && !$conversation->isClientAgreeToPay()) {
                    $interval->setPrice(0.0);
                    $interval->setModelEarnings(0.0);
                    $interval->setMinuteRate(0.0);
                    $interval->setStatus(ConversationInterval::STATUS_PAYED);
                }
                $em->flush();

                // If user does not have enough money and need to pay for message - throw an exception.
                $user = $this->getUser();
                $securityChecker = $this->container->get('security.authorization_checker');

                if ($shouldPay) {
                    if ((double)$user->getCoins() - $interval->getPrice() < 0.000001) {
                        throw new NotEnoughMoneyException();
                    }

                    $this->payNotPayedConversationIntervals($conversation);

                // if model sends a message and client agree to pay and has coins - then take the coins.
                } else if ($securityChecker->isGranted('ROLE_MODEL') && $conversation->isClientAgreeToPay()
                    && (double)$user->getCoins() >= $interval->getPrice()
                ) {
                    try {
                        $this->payNotPayedConversationIntervals($conversation);
                    } catch (NotEnoughMoneyException $ex) {
                    }
                }
            }

            $em->flush();
            $em->commit();

            $this->calculateWhoSeen($conversation);
            $this->estimateConversation($conversation);

            $companion = $conversation->getCompanion($message->getAuthor());
            if ($companion->isOnline()) {
                $this->container->get('app.queue')->enqueueNewChatMessageEvent($message);
            } else {
                $this->container->get('app.notificator')->notifyNewMessageArrived($companion, $message);
            }

        } catch (\Exception $e) {
            try {
               // $em->rollBack();
            } catch (ConnectionException $conEx) {}

            throw $e; //\ErrorException("Cannot add new message", 0, 1, __FILE__, __LINE__, $e);
        }
    }

    /**
     * @param Conversation $conversation
     */
    public function estimateConversation(Conversation $conversation)
    {
        if (!$conversation->isStalePaymentInfo()) {
            return;
        }
        $seconds = 0;
        $price = 0.0;
        $modelEarnings = 0.0;

        foreach ($this->getConversationIntervals($conversation) as $interval) {
            if ($interval->getStatus() !== ConversationInterval::STATUS_PAYED) {
                $this->estimateInterval($interval);
            }
            $seconds += (int) $interval->getSeconds();
            $price += (float) $interval->getPrice();
            $modelEarnings += (float) $interval->getModelEarnings();
        }

        $conversation->setPrice($price);
        $conversation->setSeconds($seconds);
        $conversation->setModelEarnings($modelEarnings);
        $conversation->setStalePaymentInfo(false);

        $this->getManager()->flush();
    }

    /**
     * @param ConversationInterval $interval
     * @throws \ErrorException
     */
    public function estimateInterval(ConversationInterval $interval)
    {
        $rate = (float) $this->container->getParameter('payment.minute_rate');
        $modelShare = (float) $this->container->getParameter('payment.model_share');

        if ($interval->getStatus() !== ConversationInterval::STATUS_PAYED) {
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
     * @return ConversationInterval[]
     */
    public function getConversationIntervals(Conversation $conversation)
    {
        $result = $this->getManager()->createQuery('SELECT ci, sm, em FROM AppBundle:ConversationInterval ci '
                . 'JOIN ci.startMessage sm '
                . 'JOIN ci.endMessage em '
                . 'WHERE ci.conversation = :conversation ORDER BY sm.dateAdded')
            ->setHint(Query::HINT_INCLUDE_META_COLUMNS, true)
            ->execute(['conversation' => $conversation]);

        return $result;
    }

    /**
     * @param Conversation $conversation
     * @return \AppBundle\Entity\Message[]
     */
    protected function getNotCalculatedMessages(Conversation $conversation)
    {
        /** @var Message[] $result */
        $result = $this->getManager()->createQuery('SELECT m FROM AppBundle:Message m JOIN m.conversation c '
            . 'WHERE (m.author = c.client OR m.author = c.model) AND c = :conversation '
            . 'ORDER BY m.dateAdded ASC')
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
                if ($prevMessage/* && !$message->getPreviousInterval()*/) {
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
        $result = $em->createQuery('SELECT m FROM AppBundle:Message m JOIN m.conversation c '
                . 'WHERE (m.author = c.client OR m.author = c.model) AND c = :conversation '
                . $messageCondition
                . 'ORDER BY m.dateAdded DESC, m.id DESC')
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

        $interval->setStartMessage($prevMessage);
        $interval->setEndMessage($message);

        $interval->setSeconds($interval->calculateIntervalSeconds());
        $this->estimateInterval($interval);
        $em->flush();
        $this->estimateConversation($conversation);
        return $interval;
    }

    /**
     * @param ParticipantMessage $message
     */
    public function complainMessage(ParticipantMessage $message)
    {
        if ($message->getComplaint()) {
            throw new \InvalidArgumentException('The message has already been complained.');
        }
        $complaint = new MessageComplaint();
        $complaint->setMessage($message);
        $complaint->setModel($message->getConversation()->getModel());
        $message->setComplaint($complaint);
        $this->getManager()->persist($complaint);
        $this->getManager()->flush();
    }

    /**
     * @param ParticipantMessage $message
     */
    public function markMessageDeletedByUser(ParticipantMessage $message)
    {
        $user = $this->getUser();

        if ($message->getAuthor()->getId() !== $user->getId()) {
            throw new AccessDeniedException();
        }

        if ($message->isDeletedByUser()) {
            return;
        }

        $message->setDeletedByUser(true);
        $this->getManager()->flush();
    }

    /**
     * @param Conversation $conversation
     * @return int
     */
    protected function getConversationPersonalMessagesCount(Conversation $conversation)
    {
        $em = $this->getManager();
        $result = $em->createQuery('SELECT COUNT(m) cnt FROM AppBundle:Message m JOIN m.conversation c '
            . 'WHERE (m.author = c.client OR m.author = c.model) AND c = :conversation')
            ->setMaxResults(1)
            ->execute(['conversation' => $conversation]);

        return (int) $result[0]['cnt'];
    }

    /**
     * @param Conversation $conversation
     * @return int
     */
    protected function getConversationModelMessagesCount(Conversation $conversation)
    {
        $em = $this->getManager();
        $result = $em->createQuery('SELECT COUNT(m) cnt FROM AppBundle:Message m JOIN m.conversation c '
            . 'WHERE m.author = c.model AND c = :conversation')
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
    protected function payNotPayedConversationIntervals(Conversation $conversation)
    {
        $em = $this->getManager();

        $result = $em->createQuery('SELECT ci FROM AppBundle:ConversationInterval ci '
                . 'WHERE ci.conversation = :conversation AND ci.status != :status')
            ->execute([
                'conversation' => $conversation,
                'status' => ConversationInterval::STATUS_PAYED
            ]);

        $coinService = $this->container->get('app.coins');
        foreach ($result as $interval) {
            $coinService->payConversationInterval($interval);
        }
    }

    /**
     * @param Conversation $conversation
     * @param bool $flush
     * @return Conversation
     */
    public function calculateWhoSeen(Conversation $conversation, $flush = true)
    {
        $em = $this->getManager();
        $notSeenByClientResult = $em->createQuery('SELECT COUNT(m) s FROM AppBundle:Message m JOIN m.conversation c '
                . 'WHERE c = :conversation AND m.seenByClient = FALSE')
            ->execute(['conversation' => $conversation], Query::HYDRATE_SCALAR);

        $notSeenByModelResult = $em->createQuery('SELECT COUNT(m) s FROM AppBundle:Message m JOIN m.conversation c '
                . 'WHERE c = :conversation AND m.seenByModel = FALSE')
            ->execute(['conversation' => $conversation], Query::HYDRATE_SCALAR);

        $notSeenByClient = (int) $notSeenByClientResult[0]['s'];
        $notSeenByModel = (int) $notSeenByModelResult[0]['s'];

        $conversation->setClientUnseenMessageCount($notSeenByClient);
        $conversation->setModelUnseenMessageCount($notSeenByModel);

        if ($flush) {
            $this->getManager()->flush();
        }

        return $conversation;
    }

    /**
     * @param Conversation $conversation
     * @param User $user
     * @param array $messageIds
     * @param bool $seen
     * @param bool $flush
     */
    public function markConversationMessagesSeenById(Conversation $conversation, User $user,
                                                     array $messageIds, $seen = true, $flush = true)
    {
        /** @var MessageRepository $messageRepository */
        $messageRepository = $this->getManager()->getRepository('AppBundle:Message');
        $messages = $messageRepository->getConversationMessagesById($conversation, $messageIds);

        foreach ($messages as $message) {
            $message->setSeenByUser($user, $seen);
        }

        if ($flush) {
            $this->getManager()->flush();
        }
    }

    public function checkCurrentUserShouldPay(Conversation $conversation)
    {
        $maxFirstMessages = (int) $this->container->getParameter('chat.first_messages_limit') ?: 2;
        $modelMessagesCount = $this->getConversationModelMessagesCount($conversation);

        $securityChecker = $this->container->get('security.authorization_checker');

        // If client has many messages, but did not agreed to pay for chat, rise the exception.
        $shouldPay = $securityChecker->isGranted('ROLE_CLIENT') && $modelMessagesCount >= $maxFirstMessages;

        return $shouldPay;
    }

    public function countUserTotalUnreadMessages(User $user)
    {
        $qb = $this->getManager()->createQueryBuilder();

        if ($user->hasRole('ROLE_CLIENT')) {
            $field = 'clientUnseenMessages';
            $roleField = 'client';

        } else if ($user->hasRole('ROLE_MODEL')) {
            $field = 'modelUnseenMessages';
            $roleField = 'model';

        } else {
            return 0;
        }

        $result = $qb->select("SUM(c.$field) unreadCount")
            ->from('AppBundle:Conversation', 'c')
            ->where("c.$roleField = :user")
            ->setParameter('user', $user)
            ->getQuery()->execute();

        return (int)$result[0]['unreadCount'];
    }
}