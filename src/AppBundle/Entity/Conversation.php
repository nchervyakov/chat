<?php

namespace AppBundle\Entity;

use AppBundle\Validator\Constraints\UserOwnConversation;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMSSerializer;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineConstraints;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * Conversation
 *
 * @ORM\Table(name="conversations",
 *      uniqueConstraints={
 *          @ORM\UniqueConstraint(columns={"client_id", "model_id"})
 *      }
 * )
 * @ORM\Entity(repositoryClass="AppBundle\Entity\ConversationRepository")
 * @ORM\HasLifecycleCallbacks()
 *
 * @JMSSerializer\XmlRoot("chat")
 *
 * @Assert\Callback(methods={"checkUserRoles"}, groups={"Default", "create"})
 * @UserOwnConversation(groups={"Default", "create"})
 */
class Conversation
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @JMSSerializer\Groups({"user_read", "model_stat", "chat_list", "message_list"})
     * @JMSSerializer\XmlAttribute()
     */
    private $id;

    /**
     * Client user.
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User", inversedBy="clientConversations")
     * @ORM\JoinColumn(name="client_id", referencedColumnName="id", onDelete="CASCADE")
     *
     * @JMSSerializer\Groups({"user_read", "user_write", "model_stat", "chat_list"})
     * @JMSSerializer\Type("AppBundle\Entity\User")
     * @JMSSerializer\MaxDepth(depth=1)
     *
     * @Assert\NotBlank(groups={"create"})
     */
    protected $client;

    /**
     * Model user.
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User", inversedBy="modelConversations", cascade={"remove"})
     * @ORM\JoinColumn(name="model_id", referencedColumnName="id", onDelete="CASCADE")
     *
     * @JMSSerializer\Groups({"user_read", "user_write", "chat_list"})
     * @JMSSerializer\Type("AppBundle\Entity\User")
     * @JMSSerializer\MaxDepth(depth=1)
     *
     * @Assert\NotBlank(groups={"create"})
     */
    protected $model;

    /**
     * @var integer Total seconds from start
     * @ORM\Column(name="seconds", type="integer", options={"default": 0})
     * @JMSSerializer\Expose()
     * @JMSSerializer\XmlAttribute()
     * @JMSSerializer\Groups({"user_read", "model_stat", "chat_list"})
     */
    private $seconds = 0;

    /**
     * @var float Total earnings for this chat
     * @ORM\Column(name="price", type="decimal", precision=18, scale=8, options={"default": 0.0})
     * @JMSSerializer\Expose()
     * @JMSSerializer\XmlAttribute()
     * @JMSSerializer\Groups({"client_read"})
     */
    private $price = 0.0;

    /**
     * @var float Total model earnings on this chat
     * @ORM\Column(name="model_earnings", type="decimal", precision=18, scale=8, options={"default": 0.0})
     * @JMSSerializer\XmlAttribute()
     * @JMSSerializer\Groups({"model_stat", "model_read"})
     */
    private $modelEarnings = 0.0;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_added", type="datetime", nullable=true)
     * @JMSSerializer\XmlAttribute()
     * @JMSSerializer\Groups({"user_read", "admin_write", "chat_list"})
     */
    private $dateAdded;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_updated", type="datetime", nullable=true)
     * @JMSSerializer\XmlAttribute()
     * @JMSSerializer\Groups({"user_read", "admin_write"})
     */
    private $dateUpdated;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="last_message_date", type="datetime", nullable=true)
     * @JMSSerializer\Expose()
     * @JMSSerializer\XmlAttribute()
     * @JMSSerializer\Groups({"user_read", "admin_write", "chat_list"})
     */
    private $lastMessageDate;

    /**
     * @var bool Whether the conversation was recalculated after changing the interval calculation algorithm (Version20150410151931)
     * @ORM\Column(name="recalculated", type="boolean", nullable=false, options={"default": 1})
     * @JMSSerializer\Groups({"admin_read", "admin_write"})
     */
    private $recalculated = false;

    /**
     * @var bool
     * @ORM\Column(name="client_agree_to_pay", type="boolean", nullable=false, options={"default": 0})
     * @JMSSerializer\Expose()
     * @JMSSerializer\XmlAttribute()
     * @JMSSerializer\Groups({"user_read", "admin_write", "chat_list"})
     */
    private $clientAgreeToPay = false;

    /**
     * @var bool
     * @ORM\Column(name="stale_payment_info", type="boolean", nullable=false, options={"default": 1})
     * @JMSSerializer\Exclude()
     */
    private $stalePaymentInfo = true;

    /**
     * @var int Number of messages client haven't seen
     * @ORM\Column(name="client_unseen_messages", type="integer", options={"default": 0})
     * @JMSSerializer\Expose()
     * @JMSSerializer\XmlAttribute()
     * @JMSSerializer\Groups({"user_read", "chat_list"})
     */
    private $clientUnseenMessages = 0;

    /**
     * @var int Number of messages model haven't seen
     * @ORM\Column(name="model_unseen_messages", type="integer", options={"default": 0})
     * @JMSSerializer\Expose()
     * @JMSSerializer\XmlAttribute()
     * @JMSSerializer\Groups({"user_read", "chat_list"})
     */
    private $modelUnseenMessages = 0;

    public function __construct()
    {
        $this->setDateAdded(new \DateTime());
    }

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return User
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * @param User $client
     */
    public function setClient(User $client = null)
    {
        $this->client = $client;
    }

    /**
     * @return User
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * @param User $model
     */
    public function setModel(User $model = null)
    {
        $this->model = $model;
    }

    /**
     * @return \DateTime
     */
    public function getDateAdded()
    {
        return $this->dateAdded;
    }

    /**
     * @param \DateTime $dateAdded
     */
    public function setDateAdded($dateAdded)
    {
        $this->dateAdded = $dateAdded;
    }

    /**
     * @return \DateTime
     */
    public function getDateUpdated()
    {
        return $this->dateUpdated;
    }

    /**
     * @param \DateTime $dateUpdated
     */
    public function setDateUpdated($dateUpdated)
    {
        $this->dateUpdated = $dateUpdated;
    }

    /**
     * @ORM\PreUpdate()
     */
    public function preUpdate()
    {
        $this->setDateUpdated(new \DateTime());
    }

    /**
     * Add messages
     *
     * @param Message $message
     * @return Conversation
     */
    public function addMessage(Message $message)
    {
        //$this->messages[] = $messages;
        $message->setConversation($this);
        $this->lastMessageDate = new \DateTime();

        return $this;
    }

    /**
     * Remove messages
     *
     * @param Message $message
     */
    public function removeMessage(Message $message)
    {
        $message->setConversation(null);
        //$this->messages->removeElement($messages);
    }

    /**
     * Add intervals
     *
     * @param ConversationInterval $interval
     * @return Conversation
     */
    public function addInterval(ConversationInterval $interval)
    {
        $interval->setConversation($this);

        return $this;
    }

    /**
     * Remove intervals
     *
     * @param ConversationInterval $interval
     */
    public function removeInterval(ConversationInterval $interval)
    {
        $interval->setConversation(null);
    }

    /**
     * Set lastMessageDate
     *
     * @param \DateTime $lastMessageDate
     * @return Conversation
     */
    public function setLastMessageDate($lastMessageDate)
    {
        $this->lastMessageDate = $lastMessageDate;

        return $this;
    }

    /**
     * Get lastMessageDate
     *
     * @return \DateTime 
     */
    public function getLastMessageDate()
    {
        return $this->lastMessageDate;
    }

    /**
     * Set seconds
     *
     * @param integer $seconds
     * @return Conversation
     */
    public function setSeconds($seconds)
    {
        $this->seconds = $seconds;

        return $this;
    }

    /**
     * Get seconds
     *
     * @return integer 
     */
    public function getSeconds()
    {
        return $this->seconds;
    }

    /**
     * Set price
     *
     * @param string $price
     * @return Conversation
     */
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * @param float $modelEarnings
     * @return Conversation
     */
    public function setModelEarnings($modelEarnings)
    {
        $this->modelEarnings = $modelEarnings;

        return $this;
    }

    /**
     * @return float
     */
    public function getModelEarnings()
    {
        return $this->modelEarnings;
    }

    /**
     * @return boolean
     */
    public function isRecalculated()
    {
        return $this->recalculated;
    }

    /**
     * @param boolean $recalculated
     */
    public function setRecalculated($recalculated)
    {
        $this->recalculated = (boolean) $recalculated;
    }

    /**
     * @return boolean
     */
    public function isClientAgreeToPay()
    {
        return $this->clientAgreeToPay;
    }

    /**
     * @param boolean $clientAgreeToPay
     * @return $this
     */
    public function setClientAgreeToPay($clientAgreeToPay)
    {
        $this->clientAgreeToPay = $clientAgreeToPay;

        return $this;
    }

    /**
     * @return boolean
     */
    public function isStalePaymentInfo()
    {
        return $this->stalePaymentInfo;
    }

    /**
     * @param boolean $stalePaymentInfo
     * @return $this
     */
    public function setStalePaymentInfo($stalePaymentInfo)
    {
        $this->stalePaymentInfo = $stalePaymentInfo;

        return $this;
    }

    /**
     * @return int
     */
    public function getClientUnseenMessages()
    {
        return $this->clientUnseenMessages;
    }

    /**
     * @param int $clientUnseenMessages
     * @return $this
     */
    public function setClientUnseenMessageCount($clientUnseenMessages)
    {
        $this->clientUnseenMessages = $clientUnseenMessages;

        return $this;
    }

    /**
     * @return int
     */
    public function getModelUnseenMessages()
    {
        return $this->modelUnseenMessages;
    }

    /**
     * @param int $modelUnseenMessages
     * @return $this
     */
    public function setModelUnseenMessageCount($modelUnseenMessages)
    {
        $this->modelUnseenMessages = $modelUnseenMessages;

        return $this;
    }

    /**
     * @param User $user
     * @return int
     */
    public function getUserUnseenMessageCount(User $user)
    {
        if ($user->hasRole('ROLE_CLIENT') && $this->getClient()->getId() === $user->getId()) {
            return $this->getClientUnseenMessages();

        } else if ($user->hasRole('ROLE_MODEL') && $this->getModel()->getId() === $user->getId()) {
            return $this->getModelUnseenMessages();
        }

        return 0;
    }

    /**
     * Returns a companion of the user.
     *
     * @param User $user
     * @return User
     */
    public function getCompanion(User $user)
    {
        return $this->getClient() === $user ? $this->getModel() : $this->getClient();
    }

    /**
     * @param ExecutionContextInterface $context
     */
    public function checkUserRoles(ExecutionContextInterface $context)
    {
        if (!$this->client || !$this->client->hasRole('ROLE_CLIENT')) {
            $context->buildViolation('Client must be a client user.')->addViolation();
        }

        if (!$this->model || !$this->model->hasRole('ROLE_MODEL')) {
            $context->buildViolation('Model must be a model user.')->addViolation();
        }
    }

    /**
     * @param User $user
     * @return bool
     */
    public function isParticipant(User $user)
    {
        return $this->client === $user || $this->model === $user;
    }
}
