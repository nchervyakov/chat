<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineConstraints;

/**
 * Conversation
 *
 * @ORM\Table(name="conversations")
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
     * @ORM\JoinColumn(name="initiator_id", referencedColumnName="id")
     */
    protected $initiator;

    /**
     * Acceptor (target user) of the conversation.
     * @var User
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User")
     * @ORM\JoinColumn(name="acceptor_id", referencedColumnName="id")
     */
    protected $acceptor;

    /**
     * @var Collection|array
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Message", mappedBy="conversation")
     */
    protected $messages;

    /**
     * @var ConversationInterval
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
    public function getInitiator()
    {
        return $this->initiator;
    }

    /**
     * @param User $initiator
     */
    public function setInitiator($initiator)
    {
        $this->initiator = $initiator;
    }

    /**
     * @return User
     */
    public function getAcceptor()
    {
        return $this->acceptor;
    }

    /**
     * @param User $acceptor
     */
    public function setAcceptor($acceptor)
    {
        $this->acceptor = $acceptor;
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
     * @return array|Collection
     */
    public function getMessages()
    {
        return $this->messages;
    }

    /**
     * @param array|Collection $messages
     */
    public function setMessages($messages)
    {
        $this->messages = $messages;
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
     * @param \AppBundle\Entity\Message $messages
     * @return Conversation
     */
    public function addMessage(\AppBundle\Entity\Message $messages)
    {
        $this->messages[] = $messages;

        return $this;
    }

    /**
     * Remove messages
     *
     * @param \AppBundle\Entity\Message $messages
     */
    public function removeMessage(\AppBundle\Entity\Message $messages)
    {
        $this->messages->removeElement($messages);
    }
}
