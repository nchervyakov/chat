<?php
/**
 * Created by IntelliJ IDEA.
 * User: Nikolay Chervyakov 
 * Date: 25.02.2015
 * Time: 13:28
 */


namespace AppBundle\Entity;


use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMSSerializer;
//use Sonata\UserBundle\Model\BaseGroup;
use Sonata\UserBundle\Entity\BaseGroup;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Group
 *
 * @ORM\Table(name="groups")
 * @ORM\Entity(repositoryClass="AppBundle\Entity\GroupRepository")
 * @ORM\HasLifecycleCallbacks()
 * @JMSSerializer\XmlRoot("group")
 * @JMSSerializer\ExclusionPolicy("ALL")
 */
class Group extends BaseGroup
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @JMSSerializer\XmlAttribute()
     * @JMSSerializer\Expose()
     * @JMSSerializer\Groups({"Default", "user_read", "admin_write"})
     */
    protected $id;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_added", type="datetime", nullable=true)
     * @JMSSerializer\Expose()
     * @JMSSerializer\XmlAttribute()
     * @JMSSerializer\Groups({"Default", "user_read", "admin_write"})
     */
    private $dateAdded;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_updated", type="datetime", nullable=true)
     * @JMSSerializer\Expose()
     * @JMSSerializer\XmlAttribute()
     * @JMSSerializer\Groups({"Default", "user_read", "admin_write"})
     */
    private $dateUpdated;

    /**
     * @var string Name
     * @JMSSerializer\Expose()
     * @JMSSerializer\XmlAttribute()
     * @JMSSerializer\Groups({"Default", "user_read", "admin_write"})
     */
    protected $name;

    /**
     * @var array Roles
     * @JMSSerializer\Type("array<string>")
     * @JMSSerializer\Expose()
     * @JMSSerializer\Groups({"Default", "user_read", "admin_write"})
     * @JMSSerializer\XmlList("role")
     */
    protected $roles;

    public function __construct($name, $roles = array())
    {
        parent::__construct($name, $roles);
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
     * @ORM\PreUpdate()
     */
    public function preUpdate()
    {
        $this->setDateUpdated(new \DateTime());
    }
}
