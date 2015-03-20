<?php
/**
 * Created by IntelliJ IDEA.
 * User: Nikolay Chervyakov 
 * Date: 20.03.2015
 * Time: 19:19
 */


namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * The message a potential model sends to admin in order to be registered as a model on the website.
 *
 * @package AppBundle\Entity
 * @ORM\Entity(repositoryClass="AppBundle\Entity\ModelRequestRepository")
 * @ORM\Table(name="model_requests")
 */
class ModelRequest 
{
    /**
     * @var integer
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(name="first_name", type="string", length=255)
     */
    private $firstName;

    /**
     * @var string
     * @ORM\Column(name="last_name", type="string", length=64)
     */
    private $lastName;

    /**
     * @var string
     * @ORM\Column(name="email", type="string", length=255)
     * @Assert\NotBlank()
     * @Assert\Email()
     */
    private $email;

    /**
     * @var string
     * @ORM\Column(name="facebook_url", type="string", length=255, nullable=true)
     */
    private $facebookURL;

    /**
     * @var string
     * @ORM\Column(name="instagram_url", type="string", length=255, nullable=true)
     */
    private $instagramURL;

    /**
     * @var string
     * @ORM\Column(name="message", type="text")
     * @Assert\NotBlank()
     */
    private $message = '';

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
     * Set dateAdded
     *
     * @param \DateTime $dateAdded
     * @return ModelRequest
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
     * @return ModelRequest
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
     * Set email
     *
     * @param string $email
     * @return ModelRequest
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string 
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set facebookURL
     *
     * @param string $facebookURL
     * @return ModelRequest
     */
    public function setFacebookURL($facebookURL)
    {
        $this->facebookURL = $facebookURL;

        return $this;
    }

    /**
     * Get facebookURL
     *
     * @return string 
     */
    public function getFacebookURL()
    {
        return $this->facebookURL;
    }

    /**
     * Set instagramURL
     *
     * @param string $instagramURL
     * @return ModelRequest
     */
    public function setInstagramURL($instagramURL)
    {
        $this->instagramURL = $instagramURL;

        return $this;
    }

    /**
     * Get instagramURL
     *
     * @return string 
     */
    public function getInstagramURL()
    {
        return $this->instagramURL;
    }

    /**
     * Set message
     *
     * @param string $message
     * @return ModelRequest
     */
    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * Get message
     *
     * @return string 
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Set firstName
     *
     * @param string $firstName
     * @return ModelRequest
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * Get firstName
     *
     * @return string 
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * Set lastName
     *
     * @param string $lastName
     * @return ModelRequest
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * Get lastName
     *
     * @return string 
     */
    public function getLastName()
    {
        return $this->lastName;
    }
}
