<?php
namespace AppBundle\Entity;


use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Model\GroupInterface;
use FOS\UserBundle\Model\User as BaseUser;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * User
 *
 * @ORM\Table(name="users",
 *      indexes={
 *          @ORM\Index(columns={"date_of_birth"}, name="date_of_birth"),
 *          @ORM\Index(columns={"sort_order"}, name="sort_order")
 *      }
 * )
 * @ORM\Entity(repositoryClass="AppBundle\Entity\UserRepository")
 * @UniqueEntity(fields={"emailCanonical"}, errorPath="email", message="fos_user.email.already_used",
 *      groups={"AppRegistration", "AppProfile"})
 * @ORM\HasLifecycleCallbacks()
 */
class User extends BaseUser
{
    const ROLE_CLIENT = "ROLE_CLIENT";
    const ROLE_MODEL = "ROLE_MODEL";

    const GENDER_MALE = 'male';
    const GENDER_FEMALE = 'female';

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     * @Assert\NotBlank(message="fos_user.email.blank", groups={"AppRegistration", "AppProfile"})
     * @Assert\Length(
     *      min=2, minMessage="fos_user.email.short",
     *      max=254, maxMessage="fos_user.email.long",
     *      groups={"AppRegistration", "AppProfile"})
     * @Assert\Email(message="fos_user.email.invalid", groups={"AppRegistration", "AppProfile"})
     */
    protected $email;

    /**
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\Group")
     * @ORM\JoinTable(name="users_groups",
     *      joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="group_id", referencedColumnName="id")}
     * )
     */
    protected $groups;

    /**
     * @var string
     * @ORM\Column(type="string", name="first_name", length=255, nullable=true)
     * @Assert\NotBlank(groups={"AppRegistration", "AppProfile", "Registration", "Profile"})
     */
    protected $firstName;

    /**
     * @var string
     * @ORM\Column(type="string", name="last_name", length=255, nullable=true)
     * @Assert\NotBlank(groups={"AppRegistration", "AppProfile", "Registration", "Profile"})
     */
    protected $lastName;

    /**
     * @var string
     * @ORM\Column(name="gender", type="string", nullable=true)
     * @Assert\Choice(choices={"male", "female"})
     * @Assert\NotBlank(groups={"AppRegistration"})
     */
    protected $gender;

    /**
     * @var \DateTime
     * @var \DateTime
     * @ORM\Column(name="date_of_birth", type="date", nullable=true)
     */
    protected $dateOfBirth;

    /**
     * @var string
     * @ORM\Column(name="facebook_id", type="string", nullable=true, length=64)
     */
    protected $facebookId;

    /**
     * @var string
     * @ORM\Column(name="vkontakte_id", type="string", nullable=true, length=64)
     */
    protected $vkontakteId;

    /**
     * @var string
     * @ORM\Column(name="twitter_id", type="string", nullable=true, length=64)
     */
    protected $twitterId;

    /**
     * @var string
     * @ORM\Column(name="google_id", type="string", nullable=true, length=64)
     */
    protected $googleId;

    /**
     * @var string
     * @ORM\Column(name="github_id", type="string", nullable=true, length=64)
     */
    protected $githubId;

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
     * @ORM\Column(name="sort_order", type="bigint", nullable=true, options={"default": 0})
     * @ORM\OrderBy("DESC")
     */
    private $order;

    /**
     * @var string
     * @ORM\Column(name="thumbnail", length=255, type="string", nullable=true)
     */
    private $thumbnail;

    /**
     * @var Collection|UserPhoto[]
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\UserPhoto", mappedBy="owner")
     */
    private $photos;

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->groups = new ArrayCollection();
        $this->photos = new ArrayCollection();
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
     * Set firstName
     *
     * @param string $firstName
     * @return User
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
     * @return User
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

    /**
     * Set dateOfBirth
     *
     * @param \DateTime $dateOfBirth
     * @return User
     */
    public function setDateOfBirth($dateOfBirth)
    {
        $this->dateOfBirth = $dateOfBirth;

        return $this;
    }

    /**
     * Get dateOfBirth
     *
     * @return \DateTime 
     */
    public function getDateOfBirth()
    {
        return $this->dateOfBirth;
    }

    /**
     * @return string
     */
    public function getFullName()
    {
        return $this->getFirstName() || $this->getLastName()
            ? trim($this->getFirstName() . ' ' . $this->getLastName())
            : ($this->getUserName() ? $this->getUserName() : 'Visitor');
    }

    /**
     * @return string
     */
    public function getFacebookId()
    {
        return $this->facebookId;
    }

    /**
     * @param string $facebookId
     */
    public function setFacebookId($facebookId)
    {
        $this->facebookId = $facebookId;
    }

    /**
     * @return string
     */
    public function getVkontakteId()
    {
        return $this->vkontakteId;
    }

    /**
     * @param string $vkontakteId
     */
    public function setVkontakteId($vkontakteId)
    {
        $this->vkontakteId = $vkontakteId;
    }

    /**
     * @return string
     */
    public function getTwitterId()
    {
        return $this->twitterId;
    }

    /**
     * @param string $twitterId
     */
    public function setTwitterId($twitterId)
    {
        $this->twitterId = $twitterId;
    }

    /**
     * @return string
     */
    public function getGoogleId()
    {
        return $this->googleId;
    }

    /**
     * @param string $googleId
     */
    public function setGoogleId($googleId)
    {
        $this->googleId = $googleId;
    }

    /**
     * @return string
     */
    public function getGithubId()
    {
        return $this->githubId;
    }

    /**
     * @param string $githubId
     */
    public function setGithubId($githubId)
    {
        $this->githubId = $githubId;
    }

    /**
     * Add groups
     *
     * @param Group|GroupInterface $groups
     * @return User
     */
    public function addGroup(GroupInterface $groups)
    {
        $this->groups[] = $groups;

        return $this;
    }

    /**
     * Remove groups
     *
     * @param Group|GroupInterface $group
     * @return $this|void
     */
    public function removeGroup(GroupInterface $group)
    {
        $this->groups->removeElement($group);
    }

    /**
     * Get groups
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getGroups()
    {
        return $this->groups;
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
    public function getGender()
    {
        return $this->gender;
    }

    /**
     * @param string $gender
     */
    public function setGender($gender)
    {
        $this->gender = $gender;
    }

    /**
     * @return array
     */
    public static function getGenders()
    {
        return [self::GENDER_MALE, self::GENDER_FEMALE];
    }

    /**
     * @return array
     */
    public static function getGendersLabels()
    {
        return [
            self::GENDER_MALE => 'gender.male',
            self::GENDER_FEMALE => 'gender.female'
        ];
    }

    /**
     * @ORM\PreUpdate()
     */
    public function preUpdate()
    {
        $this->setDateUpdated(new \DateTime());
    }

    /**
     * @inheritdoc
     */
    public function serialize()
    {
        return serialize(array(
            $this->password,
            $this->salt,
            $this->usernameCanonical,
            $this->username,
            $this->expired,
            $this->locked,
            $this->credentialsExpired,
            $this->enabled,
            $this->id,
            $this->firstName,
            $this->lastName,
            $this->gender,
            $this->dateOfBirth,
            $this->facebookId,
            $this->twitterId,
            $this->vkontakteId,
            $this->googleId,
            $this->githubId
        ));
    }

    /**
     * @inheritdoc
     */
    public function unserialize($serialized)
    {
        $data = unserialize($serialized);
        // add a few extra elements in the array to ensure that we have enough keys when unserializing
        // older data which does not include all properties.
        $data = array_merge($data, array_fill(0, 2, null));

        list(
            $this->password,
            $this->salt,
            $this->usernameCanonical,
            $this->username,
            $this->expired,
            $this->locked,
            $this->credentialsExpired,
            $this->enabled,
            $this->id,
            $this->firstName,
            $this->lastName,
            $this->gender,
            $this->dateOfBirth,
            $this->facebookId,
            $this->twitterId,
            $this->vkontakteId,
            $this->googleId,
            $this->githubId
            ) = $data;
    }

    /**
     * Set order
     *
     * @param integer $order
     * @return User
     */
    public function setOrder($order)
    {
        $this->order = $order;

        return $this;
    }

    /**
     * Get order
     *
     * @return integer 
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @return string
     */
    public function getThumbnail()
    {
        return $this->thumbnail;
    }

    /**
     * @param string $thumbnail
     */
    public function setThumbnail($thumbnail)
    {
        $this->thumbnail = $thumbnail;
    }

    /**
     * Calculated user age.
     * @return null|string
     */
    public function getAge()
    {
        if (!($this->dateOfBirth instanceof \DateTime)) {
            return null;
        }
        $now = new \DateTime();
        $diff = $now->diff($this->dateOfBirth);
        return $diff->format('%Y');
    }

    /**
     * Add photos
     *
     * @param UserPhoto $photos
     * @return User
     */
    public function addPhoto(UserPhoto $photos)
    {
        $this->photos[] = $photos;

        return $this;
    }

    /**
     * Remove photos
     *
     * @param UserPhoto $photos
     */
    public function removePhoto(UserPhoto $photos)
    {
        $this->photos->removeElement($photos);
    }

    /**
     * Get photos
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getPhotos()
    {
        return $this->photos;
    }
}
