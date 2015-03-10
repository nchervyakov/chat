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
     */
    protected $messages;

    /**
     * @var ConversationInterval[]|array|Collection
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
}
