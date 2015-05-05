<?php
/**
 * Created with IntelliJ IDEA by Nick Chervyakov.
 * User: Nikolay Chervyakov 
 * Date: 30.04.2015
 * Time: 17:23
  */



namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Class MessageComplaint
 * @package AppBundle\Entity
 * @ORM\Entity()
 * @ORM\Table(name="message_complaint")
 */
class MessageComplaint 
{
    const STATUS_OPEN = 'open';
    const STATUS_ACCEPTED = 'accepted';
    const STATUS_REJECTED = 'rejected';

    /**
     * @var int
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(name="id", type="integer")
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(name="status", type="string", length=16, options={"default": "open"})
     */
    private $status = self::STATUS_OPEN;

    /**
     * @var ParticipantMessage
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\ParticipantMessage", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="message_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    private $message;

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="model_id", referencedColumnName="id", nullable=false, onDelete="SET NULL")
     */
    private $model;

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="resolved_by", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     */
    private $resolvedBy;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_added", type="datetime", nullable=true)
     * @Gedmo\Timestampable(on="create")
     */
    private $dateAdded;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_updated", type="datetime", nullable=true)
     * @Gedmo\Timestampable(on="update")
     */
    private $dateUpdated;

    /**
     * @var string
     * @ORM\Column(name="added_by_ip", type="string", length=45)
     * @Gedmo\IpTraceable(on="create")
     */
    private $addedByIp;

    /**
     * MessageComplaint constructor.
     * @param ParticipantMessage $message
     * @param User $model
     */
    public function __construct(ParticipantMessage $message, User $model)
    {
        $this->message = $message;
        $this->model = $model;
    }

    /**
     * @return $this
     */
    public function reject()
    {
        if ($this->status != self::STATUS_ACCEPTED) {
            $this->status = self::STATUS_REJECTED;
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function accept()
    {
        if ($this->status != self::STATUS_REJECTED) {
            $this->status = self::STATUS_ACCEPTED;
        }

        return $this;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
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
     * @return ParticipantMessage
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param ParticipantMessage $message
     */
    public function setMessage($message)
    {
        $this->message = $message;
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
    public function setModel($model)
    {
        $this->model = $model;
    }

    /**
     * @return User
     */
    public function getResolvedBy()
    {
        return $this->resolvedBy;
    }

    /**
     * @param User $resolvedBy
     */
    public function setResolvedBy($resolvedBy)
    {
        $this->resolvedBy = $resolvedBy;
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