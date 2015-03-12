<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\Collection;
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
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var Conversation
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Conversation", inversedBy="intervals")
     * @ORM\JoinColumn(name="conversation_id", referencedColumnName="id")
     */
    private $conversation;

    /**
     * @var string
     * @ORM\Column(name="status", type="string", length=16)
     */
    private $status = self::STATUS_ACTIVE;

    /**
     * @var Message[]|Collection<Message>
     * @ORM\OneToMany(targetEntity="Message", mappedBy="interval")
     * @ORM\OrderBy({"dateAdded" = "ASC"})
     */
    private $messages;

    /**
     * @var ConversationInterval
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\ConversationInterval")
     * @ORM\JoinColumn(name="previous_interval_id", referencedColumnName="id")
     */
    private $previousInterval;

    /**
     * @var integer
     * @ORM\Column(name="seconds", type="integer", options={"default": 0})
     */
    private $seconds = 0;

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
     * Add message
     *
     * @param Message $message
     * @return ConversationInterval
     * @throws \ErrorException
     */
    public function addMessage(Message $message)
    {
        if ($this->status != self::STATUS_ACTIVE) {
            throw new \ErrorException("Cannot add a message to closed interval");
        }

        $this->messages[] = $message;
        $this->lastMessageDate = clone $message->getDateAdded();

        return $this;
    }

    /**
     * Remove message
     *
     * @param Message $message
     */
    public function removeMessage(Message $message)
    {
        $this->messages->removeElement($message);
    }

    /**
     * Get messages
     *
     * @return Collection<Message>|Collection|Message[]
     */
    public function getMessages()
    {
        return $this->messages;
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
        $lastKey = $this->messages->count() - 1;

        if ($lastKey < 0) {
            return $seconds;
        }

        // If previous closed interval exists use its last message date to start calculate current time
        $lastTime = $this->previousInterval ? $this->previousInterval->getLastMessageDate() : null;

        foreach ($this->messages as $key => $message) {
            $time = (int) $message->getDateAdded()->format('U');
            if ($lastTime === null) {
                $lastTime = $time;
                continue;
            }

            if ($time > $lastTime + self::TIME_WINDOW) {
                $seconds += self::TIME_WINDOW;

            } else {
                $seconds += self::TIME_WINDOW - ($time - $lastTime);
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
}
