<?php
/**
 * Created with IntelliJ IDEA by Nick Chervyakov.
 * User: Nikolay Chervyakov 
 * Date: 27.04.2015
 * Time: 11:39
  */



namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 * Class QueueMessage
 * @package AppBundle\Entity
 * @ORM\Entity(repositoryClass="AppBundle\Entity\QueueMessageRepository")
 * @ORM\Table(name="queue_messages")
 */
class QueueMessage 
{
    /**
     * @var integer
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(name="id", type="integer")
     * @JMS\Exclude()
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(name="name", type="string", length=64)
     * @JMS\Expose()
     */
    private $name;

    /**
     * @var mixed
     * @ORM\Column(name="data", type="json")
     * @JMS\Expose()
     */
    private $data;

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User")
     * @ORM\JoinColumn(name="target_user_id", referencedColumnName="id", nullable=false)
     * @JMS\Exclude()
     */
    private $targetUser;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_added", type="datetime", nullable=true)
     */
    private $dateAdded;

    /**
     * QueueMessage constructor.
     */
    public function __construct()
    {
        $this->dateAdded = new \DateTime();
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
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param mixed $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    /**
     * @return User
     */
    public function getTargetUser()
    {
        return $this->targetUser;
    }

    /**
     * @param User $targetUser
     */
    public function setTargetUser($targetUser)
    {
        $this->targetUser = $targetUser;
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
     * @JMS\VirtualProperty()
     */
    public function getTargetUserId()
    {
        return $this->targetUser ? $this->targetUser->getId() : null;
    }
}