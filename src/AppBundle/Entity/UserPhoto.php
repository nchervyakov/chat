<?php
/**
 * Created by IntelliJ IDEA.
 * User: Nikolay Chervyakov 
 * Date: 13.03.2015
 * Time: 13:05
 */


namespace AppBundle\Entity;


use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMSSerializer;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;


/**
 * Class UserPhoto
 * @package AppBundle\Entity
 *
 * @ORM\Entity(repositoryClass="AppBundle\Entity\UserPhotoRepository")
 * @ORM\Table(name="user_photos")
 * @ORM\HasLifecycleCallbacks()
 *
 * @Vich\Uploadable()
 *
 * @JMSSerializer\XmlRoot("user_photo")
 * @JMSSerializer\ExclusionPolicy("ALL")
 */
class UserPhoto 
{
    /**
     * @var int
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(name="id", type="integer")
     *
     * @JMSSerializer\Expose()
     * @JMSSerializer\Type("integer")
     * @JMSSerializer\Groups({"user_read"})
     * @JMSSerializer\XmlAttribute()
     */
    private $id;

    /**
     * @var UploadedFile
     * @Vich\UploadableField(mapping="user_image", fileNameProperty="fileName")
     *
     * @Assert\NotBlank(groups={"create"})
     */
    private $file;

    /**
     * @var string
     * @ORM\Column(name="file_name", type="string", length=255, options={"default": ""})
     *
     * @JMSSerializer\Expose()
     * @JMSSerializer\Type("string")
     * @JMSSerializer\Groups({"user_read"})
     * @JMSSerializer\XmlAttribute()
     */
    private $fileName = '';

    /**
     * @var string
     * @ORM\Column(name="title", type="string", length=255, options={"default": ""})
     *
     * @JMSSerializer\Expose()
     * @JMSSerializer\Type("string")
     * @JMSSerializer\Groups({"user_read"})
     * @JMSSerializer\XmlAttribute()
     */
    private $title = '';

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User", inversedBy="photos")
     * @ORM\JoinColumn(name="owner_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $owner;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_added", type="datetime", nullable=true)
     *
     * @JMSSerializer\Expose()
     * @JMSSerializer\Groups({"user_read"})
     * @JMSSerializer\XmlAttribute()
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
        $this->dateAdded = new \DateTime();
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
     * Set file
     *
     * @param UploadedFile $file
     * @return UserPhoto
     */
    public function setFile($file)
    {
        if ($file !== $this->file) {
            $this->dateUpdated = new \DateTime();
        }

        $this->file = $file;

        return $this;
    }

    /**
     * Get file
     *
     * @return UploadedFile|File
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * Set dateAdded
     *
     * @param \DateTime $dateAdded
     * @return UserPhoto
     */
    public function setDateAdded($dateAdded)
    {
        $this->dateAdded = $dateAdded;

        return $this;
    }

    /**
     * Get dateAdded
     *
     * @return \DateTime 
     */
    public function getDateAdded()
    {
        return $this->dateAdded;
    }

    /**
     * Set dateUpdated
     *
     * @param \DateTime $dateUpdated
     * @return UserPhoto
     */
    public function setDateUpdated($dateUpdated)
    {
        $this->dateUpdated = $dateUpdated;

        return $this;
    }

    /**
     * Get dateUpdated
     *
     * @return \DateTime 
     */
    public function getDateUpdated()
    {
        return $this->dateUpdated;
    }

    /**
     * @return string
     */
    public function getFileName()
    {
        return $this->fileName;
    }

    /**
     * @param string $fileName
     */
    public function setFileName($fileName)
    {
        $this->fileName = $fileName;
    }

    /**
     * @ORM\PreUpdate()
     */
    public function preUpdate()
    {
        $this->setDateUpdated(new \DateTime());
    }

    /**
     * Set title
     *
     * @param string $title
     * @return UserPhoto
     */
    public function setTitle($title)
    {
        $this->title = ''.$title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string 
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set owner
     *
     * @param User $owner
     * @return UserPhoto
     */
    public function setOwner(User $owner = null)
    {
        $this->owner = $owner;

        return $this;
    }

    /**
     * Get owner
     *
     * @return User
     */
    public function getOwner()
    {
        return $this->owner;
    }
}
