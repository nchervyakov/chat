<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serialize;

/**
 * Message
 *
 * @ORM\Table(name="messages")
 * @ORM\Entity(repositoryClass="AppBundle\Entity\MessageRepository")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="discriminator", type="string", length=32)
 * @ORM\DiscriminatorMap({
 *      "text": "AppBundle\Entity\TextMessage",
 *      "image": "AppBundle\Entity\ImageMessage",
 * })
 * @ORM\HasLifecycleCallbacks()
 */
abstract class Message
{
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
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Conversation")
     * @ORM\JoinColumn(name="conversation_id", referencedColumnName="id", onDelete="CASCADE")
     * @Serialize\MaxDepth(0)
     */
    private $conversation;

//    /**
//     * @var ConversationInterval
//     * @ORM\OneToOne(targetEntity="AppBundle\Entity\ConversationInterval", mappedBy="startMessage", fetch="LAZY")
//     * @Serialize\MaxDepth(0)
//     */
//    private $followingInterval;
//
//    /**
//     * @var ConversationInterval
//     * @ORM\OneToOne(targetEntity="AppBundle\Entity\ConversationInterval", mappedBy="endMessage", fetch="LAZY")
//     * @Serialize\MaxDepth(0)
//     */
//    private $previousInterval;

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
     * @var string
     * @ORM\Column(name="content", type="text", nullable=true)
     */
    private $content;

    /**
     * @var boolean
     * @ORM\Column(name="deleted_by_user", type="boolean", options={"default": false})
     */
    private $deletedByUser = false;

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="author_id", referencedColumnName="id", onDelete="CASCADE")
     * @Serialize\MaxDepth(0)
     */
    private $author;

    function __construct($content = null)
    {
        $this->content = $content;
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
     * @return Conversation
     */
    public function getConversation()
    {
        return $this->conversation;
    }

    /**
     * @param Conversation $conversation
     */
    public function setConversation($conversation = null)
    {
        $this->conversation = $conversation;
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
     * Set author
     *
     * @param User $author
     * @return Message
     */
    public function setAuthor(User $author = null)
    {
        $this->author = $author;

        return $this;
    }

    /**
     * Get author
     *
     * @return User
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param string $content
     */
    public function setContent($content)
    {
        $this->content = $content;
    }

    /**
     * @ORM\PreUpdate()
     */
    public function preUpdate()
    {
        $this->setDateUpdated(new \DateTime());
    }

//    /**
//     * Set interval
//     *
//     * @param ConversationInterval $followingInterval
//     * @return Message
//     */
//    public function setFollowingInterval(ConversationInterval $followingInterval = null)
//    {
//        $this->followingInterval = $followingInterval;
//
//        return $this;
//    }

//    /**
//     * Get interval
//     *
//     * @return ConversationInterval
//     */
//    public function getFollowingInterval()
//    {
//        return $this->followingInterval;
//    }
//
//    /**
//     * @return ConversationInterval
//     */
//    public function getPreviousInterval()
//    {
//        return $this->previousInterval;
//    }

//    /**
//     * @param ConversationInterval $previousInterval
//     *
//     * @return ConversationInterval
//     */
//    public function setPreviousInterval($previousInterval)
//    {
//        $this->previousInterval = $previousInterval;
//
//        return $this;
//    }

    /**
     * Set deletedByUser
     *
     * @param boolean $deletedByUser
     * @return Message
     */
    public function setDeletedByUser($deletedByUser)
    {
        $this->deletedByUser = $deletedByUser;

        return $this;
    }

    /**
     * Get deletedByUser
     *
     * @return boolean
     */
    public function isDeletedByUser()
    {
        return $this->deletedByUser;
    }

    /**
     * Get deletedByUser
     *
     * @return boolean 
     */
    public function getDeletedByUser()
    {
        return $this->deletedByUser;
    }
}
