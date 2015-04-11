<?php

namespace AppBundle\Entity;


use Doctrine\ORM\Mapping as ORM;

/**
 * ConversationInterval
 *
 * @ORM\Table(name="conversation_intervals")
 * @ORM\Entity(repositoryClass="AppBundle\Entity\ConversationIntervalRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class ConversationInterval
{
    const STATUS_ACTIVE = 'active'; // Messages can be added
    const STATUS_FIXED = 'fixed';   // Interval is ready to be payed
    const STATUS_PAYED = 'payed';   // Interval is payed

    const TIME_WINDOW = 30;         // Number of seconds after the message to be

    /**
     * @var integer
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(name="id", type="integer")
     */
    private $id;

    /**
     * @var Conversation
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Conversation", inversedBy="intervals")
     * @ORM\JoinColumn(name="conversation_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $conversation;

    /**
     * @var string
     * @ORM\Column(name="status", type="string", length=16)
     */
    private $status = self::STATUS_ACTIVE;

    /**
     * @var Message
     * @ORM\OneToOne(targetEntity="Message", mappedBy="followingInterval", cascade={"remove"}, orphanRemoval=true)
     * @ORM\OrderBy({"dateAdded" = "ASC"})
     */
    private $startMessage;

    /**
     * @var Message
     * @ORM\OneToOne(targetEntity="Message", mappedBy="previousInterval", cascade={"remove"}, orphanRemoval=true)
     * @ORM\OrderBy({"dateAdded" = "ASC"})
     */
    private $endMessage;

    /**
     * @var ConversationInterval
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\ConversationInterval")
     * @ORM\JoinColumn(name="previous_interval_id", referencedColumnName="id", onDelete="SET NULL")
     */
    private $previousInterval;

    /**
     * @var integer
     * @ORM\Column(name="seconds", type="integer", options={"default": 0})
     */
    private $seconds = 0;

    /**
     * @var float
     * @ORM\Column(name="price", type="decimal", scale=2, options={"default": 0.0})
     */
    private $price;

    /**
     * @var float
     * @ORM\Column(name="minute_rate", type="decimal", scale=2, options={"default": 0.0})
     */
    private $minuteRate = 0.0;

    /**
     * @var float
     * @ORM\Column(name="model_share", type="decimal", scale=2, options={"default": 0.0})
     */
    private $modelShare = 0.0;

    /**
     * @var float
     * @ORM\Column(name="model_earnings", type="decimal", scale=2, options={"default": 0.0})
     */
    private $modelEarnings = 0.0;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_added", type="datetime", nullable=true)
     */
    private $dateAdded;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_updated", type="datetime", nullable=true)
     */
    private $dateUpdated;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="last_message_date", type="datetime", nullable=true)
     */
    private $lastMessageDate;

    function __construct()
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
     * @return Conversation
     */
    public function getConversation()
    {
        return $this->conversation;
    }

    /**
     * @param Conversation $conversation
     */
    public function setConversation($conversation)
    {
        $this->conversation = $conversation;
    }

    /**
     * @ORM\PreUpdate()
     */
    public function preUpdate()
    {
        $this->setDateUpdated(new \DateTime());
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * Set interval status as closed
     */
    public function setClosed()
    {
        $this->setStatus(self::STATUS_FIXED);
    }

    /**
     * Set a start message
     *
     * @param Message $message
     * @return ConversationInterval
     * @throws \ErrorException
     */
    public function setStartMessage(Message $message = null)
    {
        if ($this->status != self::STATUS_ACTIVE) {
            throw new \ErrorException("Cannot add a message to closed interval");
        }

        $this->startMessage = $message;
        if (!$this->endMessage) {
            $this->lastMessageDate = clone $message->getDateAdded();
        }
        return $this;
    }

    /**
     * Get messages
     *
     * @return Message|null
     */
    public function getStartMessage()
    {
        return $this->startMessage;
    }

    /**
     * @return Message|null
     */
    public function getEndMessage()
    {
        return $this->endMessage;
    }

    /**
     * @param Message $message
     * @return $this
     * @throws \ErrorException
     */
    public function setEndMessage(Message $message = null)
    {
        if ($this->status != self::STATUS_ACTIVE) {
            throw new \ErrorException("Cannot change a closed interval");
        }

        $this->endMessage = $message;
        $this->lastMessageDate = clone $message->getDateAdded();
        return $this;
    }

    /**
     * Set seconds
     *
     * @param integer $seconds
     * @return ConversationInterval
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
     * Calculates total seconds for interval.
     * The scheme of calculating is next:
     *
     * 0---------
     * |   0---------
     * |            |   0---------
     * |            |   |        0---------
     * |            |   |                 |         0---------
     * |            |   |                 |         |
     * ************** + *******************
     *
     * The last message is not counted because its adding date just serves as a CURRENT interval UPPER boundary,
     * and also as a starting time (LOWER boundary) for the NEXT interval.
     *
     * @return int
     */
    public function calculateIntervalSeconds()
    {
        $seconds = 0; // Interval seconds
        $lastKey = $this->startMessage->count() - 1;

        if ($lastKey < 0) {
            return $seconds;
        }

        // If previous closed interval exists use its last message date to start calculate current time
        $lastTime = $this->previousInterval ? $this->previousInterval->getLastMessageDate() : null;

        foreach ($this->startMessage as $key => $message) {
            $time = (int) $message->getDateAdded()->format('U');
            if ($lastTime === null) {
                $lastTime = $time;
                continue;
            }

            if ($time > $lastTime + self::TIME_WINDOW) {
                $seconds += self::TIME_WINDOW;

            } else {
                $seconds += $time - $lastTime;
            }

            $lastTime = $time;
        }

        return $seconds;
    }

    /**
     * Set lastMessageDate
     *
     * @param \DateTime $lastMessageDate
     * @return ConversationInterval
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
     * Set previousInterval
     *
     * @param ConversationInterval $previousInterval
     * @return ConversationInterval
     */
    public function setPreviousInterval(ConversationInterval $previousInterval = null)
    {
        $this->previousInterval = $previousInterval;

        return $this;
    }

    /**
     * Get previousInterval
     *
     * @return ConversationInterval
     */
    public function getPreviousInterval()
    {
        return $this->previousInterval;
    }

    /**
     * Set price
     *
     * @param float $price
     * @return ConversationInterval
     * @throws \ErrorException
     */
    public function setPrice($price)
    {
        if ($this->status == self::STATUS_PAYED) {
            throw new \ErrorException("Cannot modify the price if interval is already paid.");
        }

        $this->price = $price;

        return $this;
    }

    /**
     * Get price
     *
     * @return float
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * Set minuteRate
     *
     * @param string $minuteRate
     * @return ConversationInterval
     */
    public function setMinuteRate($minuteRate)
    {
        $this->minuteRate = $minuteRate;

        return $this;
    }

    /**
     * Get minuteRate
     *
     * @return string 
     */
    public function getMinuteRate()
    {
        return $this->minuteRate;
    }

    /**
     * Set modelShare
     *
     * @param string $modelShare
     * @return ConversationInterval
     */
    public function setModelShare($modelShare)
    {
        $this->modelShare = $modelShare;

        return $this;
    }

    /**
     * Get modelShare
     *
     * @return string 
     */
    public function getModelShare()
    {
        return $this->modelShare;
    }

    /**
     * Set modelEarnings
     *
     * @param float $modelEarnings
     * @return ConversationInterval
     */
    public function setModelEarnings($modelEarnings)
    {
        $this->modelEarnings = $modelEarnings;

        return $this;
    }

    /**
     * Get modelEarnings
     *
     * @return float
     */
    public function getModelEarnings()
    {
        return $this->modelEarnings;
    }
}
