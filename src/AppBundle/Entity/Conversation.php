<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineConstraints;

/**
 * Conversation
 *
 * @ORM\Table(name="conversations",
 *      uniqueConstraints={
 *          @ORM\UniqueConstraint(columns={"user1_id", "user2_id"})
 *      }
 * )
 * @ORM\Entity(repositoryClass="AppBundle\Entity\ConversationRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Conversation
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * Initiator of the conversation.
     * @var User
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User")
     * @ORM\JoinColumn(name="user1_id", referencedColumnName="id")
     */
    protected $user1;

    /**
     * Acceptor (target user) of the conversation.
     * @var User
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User")
     * @ORM\JoinColumn(name="user2_id", referencedColumnName="id")
     */
    protected $user2;

    /**
     * @var Collection|array|Message[]
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Message", mappedBy="conversation")
     * @ORM\OrderBy({"dateAdded" = "ASC"})
     */
    protected $messages;

    /**
     * @var ConversationInterval[]|array|Collection
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\ConversationInterval", mappedBy="conversation")
     *
     */
    protected $intervals;

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
        $this->messages = new ArrayCollection();
        $this->intervals = new ArrayCollection();
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
    public function getUser1()
    {
        return $this->user1;
    }

    /**
     * @param User $user1
     */
    public function setUser1($user1)
    {
        $this->user1 = $user1;
    }

    /**
     * @return User
     */
    public function getUser2()
    {
        return $this->user2;
    }

    /**
     * @param User $user2
     */
    public function setUser2($user2)
    {
        $this->user2 = $user2;
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
     * @return array|Collection|Message[]
     */
    public function getMessages()
    {
        return $this->messages;
    }

    /**
     * @param array|Collection|Message[] $messages
     */
    public function setMessages($messages)
    {
        $this->messages = $messages instanceof Collection ? $messages : new ArrayCollection($messages);
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
     * @param Message $messages
     * @return Conversation
     */
    public function addMessage(Message $messages)
    {
        $this->messages[] = $messages;
        $this->lastMessageDate = new \DateTime();

        return $this;
    }

    /**
     * Remove messages
     *
     * @param Message $messages
     */
    public function removeMessage(Message $messages)
    {
        $this->messages->removeElement($messages);
    }

    /**
     * Fetches current active interval or null
     *
     * @return ConversationInterval|mixed|null
     */
    public function getActiveInterval()
    {
        foreach ($this->getIntervals() as $interval) {
            if ($interval->getStatus() === ConversationInterval::STATUS_ACTIVE) {
                return $interval;
            }
        }

        return null;
    }

    /**
     * Add intervals
     *
     * @param ConversationInterval $intervals
     * @return Conversation
     */
    public function addInterval(ConversationInterval $intervals)
    {
        $this->intervals[] = $intervals;

        return $this;
    }

    /**
     * Remove intervals
     *
     * @param ConversationInterval $intervals
     */
    public function removeInterval(ConversationInterval $intervals)
    {
        $this->intervals->removeElement($intervals);
    }

    /**
     * Get intervals
     *
     * @return \Doctrine\Common\Collections\Collection|ConversationInterval[]
     */
    public function getIntervals()
    {
        return $this->intervals;
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
     * @return User|null
     */
    public function getClient()
    {
        if ($this->user1 && $this->user1->hasRole('ROLE_CLIENT')) {
            return $this->user1;

        } else if ($this->user2 && $this->user2->hasRole('ROLE_CLIENT')) {
            return $this->user2;

        } else {
            return null;
        }
    }

    /**
     * Get total conversation time in seconds.
     *
     * @return int
     */
    public function getTotalTime()
    {
        $time = 0;
        foreach ($this->getIntervals() as $interval) {
            $time += $interval->getSeconds();
        }

        return $time;
    }

    /**
     * Get total conversation time as \DateInterval instance.
     *
     * @return bool|\DateInterval
     */
    public function getTotalTimeInterval()
    {
        $time = $this->getTotalTime();

        $now = new \DateTime();
        $before = new \DateTime('-' . $time . ' seconds');

        return $now->diff($before);
    }
}
