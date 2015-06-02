<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation as JMSSerializer;
use Symfony\Component\Validator\Constraints as Assert;

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
 * @ORM\HasLifecycleCallbacks()
 *
 * @JMSSerializer\Discriminator(field="discriminator", disabled=false, map={
 *      "text": "AppBundle\Entity\TextMessage",
 *      "image": "AppBundle\Entity\ImageMessage",
 *      "participant": "AppBundle\Entity\ParticipantMessage",
 *      "notification": "AppBundle\Entity\NotificationMessage"
 * })
 *
 * @JMSSerializer\ExclusionPolicy("ALL")
 */
abstract class Message
{
    /**
     * @var integer
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(name="id", type="integer")
     *
     * @JMSSerializer\Expose()
     * @JMSSerializer\Groups({"user_read", "message_list"})
     * @JMSSerializer\Type("integer")
     */
    private $id;

    /**
     * @var Conversation
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Conversation")
     * @ORM\JoinColumn(name="conversation_id", referencedColumnName="id", onDelete="CASCADE")
     *
     * @JMSSerializer\MaxDepth(1)
     * @JMSSerializer\Type("AppBundle\Entity\Conversation")
     * @JMSSerializer\Expose()
     * @JMSSerializer\Groups({"user_read", "message_list"})
     *
     * @Assert\NotBlank(groups={"Default"})
     */
    private $conversation;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_added", type="datetime", nullable=true)
     *
     * @JMSSerializer\Expose()
     * @JMSSerializer\Type("DateTime")
     * @JMSSerializer\Groups({"user_read", "message_list"})
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
     *
     * @ORM\Column(name="content", type="text", nullable=true)
     *
     * @JMSSerializer\Expose()
     * @JMSSerializer\Groups({"user_read", "message_list"})
     * @JMSSerializer\Type("string")
     */
    protected $content;

    /**
     * @var boolean
     *
     * @ORM\Column(name="deleted_by_user", type="boolean", options={"default": false})
     *
     * @JMSSerializer\Expose()
     * @JMSSerializer\Groups({"user_read", "message_list"})
     * @JMSSerializer\Type("boolean")
     */
    private $deletedByUser = false;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="author_id", referencedColumnName="id", onDelete="CASCADE")
     *
     * @JMSSerializer\MaxDepth(1)
     * @JMSSerializer\Expose()
     * @JMSSerializer\Groups({"user_read", "message_list"})
     */
    private $author;

    /**
     * @var bool
     * @ORM\Column(name="is_seen_by_client", type="boolean", options={"default": 0})
     *
     * @JMSSerializer\Expose()
     * @JMSSerializer\Type("boolean")
     * @JMSSerializer\Groups({"user_read", "message_list"})
     */
    private $seenByClient = false;

    /**
     * @var bool
     * @ORM\Column(name="is_seen_by_model", type="boolean", options={"default": 0})
     *
     * @JMSSerializer\Expose()
     * @JMSSerializer\Type("boolean")
     * @JMSSerializer\Groups({"user_read"})
     */
    private $seenByModel = false;

    /**
     * @var string
     * @ORM\Column(name="added_by_ip", type="string", length=45)
     * @Gedmo\IpTraceable(on="create")
     */
    private $addedByIp;

    /**
     * @var MessageComplaint
     *
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\MessageComplaint", mappedBy="message")
     *
     * @JMSSerializer\Expose()
     * @JMSSerializer\Type("AppBundle\Entity\MessageComplaint")
     * @JMSSerializer\Groups({"user_read", "message_list"})
     */
    private $complaint;

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
     * @JMSSerializer\VirtualProperty()
     * @JMSSerializer\Groups({"user_read", "message_list"})
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

    /**
     * @return MessageComplaint
     */
    public function getComplaint()
    {
        return $this->complaint;
    }

    /**
     * @param MessageComplaint $complaint
     */
    public function setComplaint($complaint)
    {
        $this->complaint = $complaint;
    }

    function __toString()
    {
        return $this->content;
    }
}
