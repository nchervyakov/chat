<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serialize;
use Gedmo\Mapping\Annotation as Gedmo;

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
 *      "participant": "AppBundle\Entity\ParticipantMessage",
 *      "notification": "AppBundle\Entity\NotificationMessage"
 * })
 * @Serialize\Discriminator(field="discriminator", map={
 *      "text": "AppBundle\Entity\TextMessage",
 *      "image": "AppBundle\Entity\ImageMessage",
 *      "participant": "AppBundle\Entity\ParticipantMessage",
 *      "notification": "AppBundle\Entity\NotificationMessage"
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
     * @Serialize\Expose()
     */
    private $id;

    /**
     * @var Conversation
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Conversation")
     * @ORM\JoinColumn(name="conversation_id", referencedColumnName="id", onDelete="CASCADE")
     * @Serialize\MaxDepth(0)
     * @Serialize\Type("AppBundle\Entity\Conversation")
     * @Serialize\Exclude()
     */
    private $conversation;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_added", type="datetime", nullable=true)
     * @Serialize\Expose()
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
     * @Serialize\Expose()
     */
    protected $content;

    /**
     * @var boolean
     * @ORM\Column(name="deleted_by_user", type="boolean", options={"default": false})
     * @Serialize\Exclude()
     */
    private $deletedByUser = false;

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="author_id", referencedColumnName="id", onDelete="CASCADE")
     * @Serialize\MaxDepth(0)
     * @Serialize\Exclude()
     */
    private $author;

    /**
     * @var bool
     * @ORM\Column(name="is_seen_by_client", type="boolean", options={"default": 0})
     * @Serialize\Exclude()
     */
    private $seenByClient = false;

    /**
     * @var bool
     * @ORM\Column(name="is_seen_by_model", type="boolean", options={"default": 0})
     * @Serialize\Exclude()
     */
    private $seenByModel = false;

    /**
     * @var string
     * @ORM\Column(name="added_by_ip", type="string", length=45)
     * @Gedmo\IpTraceable(on="create")
     */
    private $addedByIp;

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

    /**
     * @return boolean
     */
    public function isSeenByClient()
    {
        return $this->seenByClient;
    }

    /**
     * @param boolean $seenByClient
     * @return $this
     */
    public function setSeenByClient($seenByClient = true)
    {
        $this->seenByClient = $seenByClient;

        return $this;
    }

    /**
     * @return boolean
     */
    public function isSeenByModel()
    {
        return $this->seenByModel;
    }

    /**
     * @param boolean $seenByModel
     * @return $this
     */
    public function setSeenByModel($seenByModel = true)
    {
        $this->seenByModel = $seenByModel;

        return $this;
    }

    /**
     * @param bool $seen
     * @return $this|void
     */
    public function setSeenByAuthor($seen = true)
    {
        if (!$this->author) {
            return $this;
        }

        $this->setSeenByUser($this->author, $seen);
        return $this;
    }

    /**
     * @param User $user
     * @param bool $seen
     * @return $this
     */
    public function setSeenByUser(User $user, $seen = true)
    {
        if (!$this->conversation) {
            return $this;
        }

        if ($user->hasRole('ROLE_CLIENT') && $this->conversation->getClient() === $user) {
            $this->setSeenByClient($seen);

        } else if ($user->hasRole('ROLE_MODEL') && $this->conversation->getModel() === $user) {
            $this->setSeenByModel($seen);
        }

        return $this;
    }

    /**
     * @param User $user
     * @return bool
     */
    public function isSeenByUser(User $user = null)
    {
        if ($user === null || !$this->conversation) {
            return false;
        }

        if ($user->hasRole('ROLE_CLIENT') && $this->conversation->getClient() === $user) {
            return $this->seenByClient;

        } else if ($user->hasRole('ROLE_MODEL') && $this->conversation->getModel() === $user) {
            return $this->seenByModel;
        }

        return false;
    }

    /**
     * @return int|null
     * @Serialize\VirtualProperty()
     */
    public function getConversationId()
    {
        return $this->conversation ? $this->conversation->getId() : null;
    }

    /**
     * @return string
     */
    public function getAddedByIp()
    {
        return $this->addedByIp;
    }

    /**
     * @param string $addedByIp
     */
    public function setAddedByIp($addedByIp)
    {
        $this->addedByIp = $addedByIp;
    }
}
