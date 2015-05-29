<?php
namespace AppBundle\Entity;


use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Sonata\UserBundle\Model\BaseUser;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as JMSSerializer;

/**
 * User
 *
 * @ORM\Table(name="users",
 *      indexes={
 *          @ORM\Index(columns={"date_of_birth"}, name="date_of_birth"),
 *          @ORM\Index(columns={"sort_order"}, name="sort_order"),
 *          @ORM\Index(columns={"firstname"}, name="first_name"),
 *          @ORM\Index(columns={"lastname"}, name="last_name")
 *      }
 * )
 * @ORM\Entity(repositoryClass="AppBundle\Entity\UserRepository")
 * @UniqueEntity(fields={"emailCanonical"}, errorPath="email", message="fos_user.email.already_used",
 *      groups={"AppRegistration", "AppProfile"})
 * @ORM\HasLifecycleCallbacks()
 *
 * @JMSSerializer\ExclusionPolicy("ALL")
 * @JMSSerializer\XmlRoot("user")
 */
class User extends BaseUser
{
    const ROLE_CLIENT = "ROLE_CLIENT";
    const ROLE_MODEL = "ROLE_MODEL";

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @JMSSerializer\Expose()
     * @JMSSerializer\Groups({"user_read"})
     * @JMSSerializer\XmlAttribute()
     */
    protected $id;

    /**
     * @var string
     *
     * @Assert\NotBlank(message="fos_user.email.blank", groups={"AppRegistration", "AppProfile"})
     * @Assert\Length(
     *      min=2, minMessage="fos_user.email.short",
     *      max=254, maxMessage="fos_user.email.long",
     *      groups={"AppRegistration", "AppProfile"})
     * @Assert\Email(message="fos_user.email.invalid", groups={"AppRegistration", "AppProfile"})
     *
     * @JMSSerializer\Groups({"user_read"})
     * @JMSSerializer\Expose()
     * @JMSSerializer\XmlAttribute()
     */
    protected $email;

    /**
     * @var string
     * @JMSSerializer\Exclude()
     * @JMSSerializer\Groups({"admin_read", "admin_write"})
     */
    protected $password;

    /**
     * @var string
     * @JMSSerializer\Exclude()
     * @JMSSerializer\Groups({"admin_read", "admin_write"})
     */
    protected $passwordCanonical;

    /**
     * @var Group[]
     *
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\Group")
     * @ORM\JoinTable(name="users_groups",
     *      joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="group_id", referencedColumnName="id", onDelete="CASCADE")}
     * )
     *
     * @JMSSerializer\Expose()
     * @JMSSerializer\Groups({"Default", "user_read", "admin_write"})
     * @JMSSerializer\XmlList("group")
     * @JMSSerializer\Type("array<AppBundle\Entity\Group>")
     * @JMSSerializer\MaxDepth(depth=1)
     */
    protected $groups;

    /**
     * @var string
     * @ORM\Column(name="facebook_id", type="string", nullable=true, length=64, unique=true)
     */
    private $facebookId;

    /**
     * @var string
     * @ORM\Column(name="vkontakte_id", type="string", nullable=true, length=64, unique=true)
     */
    private $vkontakteId;

    /**
     * @var string
     * @ORM\Column(name="twitter_id", type="string", nullable=true, length=64, unique=true)
     */
    private $twitterId;

    /**
     * @var string
     * @ORM\Column(name="google_id", type="string", nullable=true, length=64, unique=true)
     */
    private $googleId;

    /**
     * @var string
     * @ORM\Column(name="github_id", type="string", nullable=true, length=64, unique=true)
     */
    private $githubId;

    /**
     * @var string
     * @ORM\Column(name="instagram_id", type="string", nullable=true, length=64, unique=true)
     */
    private $instagramId;

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
     * @ORM\Column(name="sort_order", type="bigint", nullable=true, options={"default": 0})
     * @ORM\OrderBy("DESC")
     * @JMSSerializer\Expose()
     * @JMSSerializer\XmlAttribute()
     * @JMSSerializer\Groups({"Default", "user_read", "admin_write"})
     */
    private $order;

    /**
     * @var UserPhoto
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\UserPhoto", cascade={"remove", "persist", "merge"}, orphanRemoval=true)
     * @ORM\JoinColumn(name="thumbnail_id", referencedColumnName="id", onDelete="SET NULL")
     * @JMSSerializer\MaxDepth(depth=1)
     * @JMSSerializer\Expose()
     * @JMSSerializer\Groups({"Default", "user_read"})
     */
    private $thumbnail;

    /**
     * @var Collection|UserPhoto[]
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\UserPhoto", mappedBy="owner", cascade={"merge", "persist", "remove"})
     * @JMSSerializer\Type("array<AppBundle\Entity\UserPhoto>")
     * @JMSSerializer\Expose()
     */
    private $photos;

    /**
     * @var string
     * @ORM\Column(name="facebook_url", type="string", nullable=true, length=255)
     */
    private $facebookURL;

    /**
     * @var string
     * @ORM\Column(name="instagram_url", type="string", nullable=true, length=255)
     */
    private $instagramURL;

    /**
     * @var bool Whether the user (model) is activated.
     * @ORM\Column(name="activated", type="boolean", options={"default": "1"})
     *
     * @JMSSerializer\XmlAttribute()
     * @JMSSerializer\Expose()
     * @JMSSerializer\Groups({"Default", "user_read", "admin_read", "admin_write"})
     */
    private $activated = true;

    /**
     * @var string
     * @ORM\Column(name="activation_token", type="string", length=64, nullable=true)
     * @JMSSerializer\Expose()
     * @JMSSerializer\Groups({"admin_read", "admin_write"})
     * @JMSSerializer\XmlAttribute()
     */
    private $activationToken;

    /**
     * Is the model notified about either she is registered by admin, or about she is activated.
     * @var bool
     * @ORM\Column(name="model_notified", type="boolean", options={"default": 0})
     * @JMSSerializer\Expose()
     * @JMSSerializer\Groups({"admin_read", "admin_write"})
     * @JMSSerializer\XmlAttribute()
     */
    private $modelNotified = false;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="last_visited_date", type="datetime", nullable=true)
     * @JMSSerializer\Expose()
     * @JMSSerializer\Groups({"user_read", "admin_write"})
     * @JMSSerializer\XmlAttribute()
     */
    private $lastVisitedDate;

    /**
     * @var bool
     * @ORM\Column(name="is_online", type="boolean", options={"default": 0}, nullable=false)
     * @JMSSerializer\Expose()
     * @JMSSerializer\Groups({"user_read", "admin_write"})
     * @JMSSerializer\XmlAttribute()
     */
    private $online = false;

    /**
     * @var float
     * @ORM\Column(name="coins", type="decimal", precision=18, scale=8, nullable=false, options={"default": 0.0})
     * @JMSSerializer\XmlAttribute()
     */
    private $coins = 0.0;

    /**
     * @var string
     * @JMSSerializer\Expose()
     * @JMSSerializer\XmlAttribute()
     * @JMSSerializer\Groups({"Default", "user_read", "admin_write"})
     *
     * @Assert\NotBlank(groups={"Default", "AppRegistration", "AppProfile"}, message="Name cannot be blank")
     */
    protected $firstname;

    /**
     * @var string
     *
     * @JMSSerializer\Expose()
     * @JMSSerializer\XmlAttribute()
     * @JMSSerializer\Groups({"Default", "user_read", "admin_write"})
     *
     * @Assert\NotBlank(groups={"Default", "AppRegistration", "AppProfile"}, message="Last name cannot be blank")
     */
    protected $lastname;

    /**
     * Used for DQL Queries
     * @var Conversation[]
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Conversation", mappedBy="client")
     */
    private $clientConversations;

    /**
     * Used for DQL Queries
     * @var Conversation[]
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Conversation", mappedBy="model")
     */
    private $modelConversations;

    /**
     * @var OAuthRequest[]
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\OAuthRequest", mappedBy="user")
     */
    private $oauthRequests;

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->photos = new ArrayCollection();
        $this->oauthRequests = new ArrayCollection();
        $this->modelConversations = new ArrayCollection();
        $this->clientConversations = new ArrayCollection();
        $birthDay = new \DateTime();
        $birthDay->modify('- 20 years');
        $this->setDateOfBirth($birthDay);
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
     * @return string
     */
    public function getFullName()
    {
        return $this->getFirstname() || $this->getLastname()
            ? trim($this->getFirstName() . ' ' . $this->getLastname())
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
            self::GENDER_FEMALE => 'gender.female',
        ];
    }

    public function getGenderLabel()
    {
        $labels = $this->getGendersLabels();
        return $labels[$this->gender];
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
            $this->firstname,
            $this->lastname,
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
            $this->firstname,
            $this->lastname,
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
     * @return UserPhoto|null
     */
    public function getThumbnail()
    {
        return $this->thumbnail;
    }

    /**
     * @param UserPhoto|string $thumbnail
     */
    public function setThumbnail(UserPhoto $thumbnail = null)
    {
        if (!$thumbnail) {
            $this->thumbnail = $thumbnail;
            return;
        }
        if (!$this->photos->contains($thumbnail)) {
            $this->addPhoto($thumbnail);
        }

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
     * @param UserPhoto $photo
     * @return User
     */
    public function addPhoto(UserPhoto $photo)
    {
        $photo->setOwner($this);
        $this->photos[] = $photo;

        if (!$this->thumbnail) {
            $this->thumbnail = $photo;
        }

        return $this;
    }

    /**
     * Remove photos
     *
     * @param UserPhoto $photo
     */
    public function removePhoto(UserPhoto $photo)
    {
        if ($this->thumbnail && $this->thumbnail == $photo) {
            $this->photos->removeElement($photo);
            $this->thumbnail = $this->photos[0] ?: null;

        } else {
            $this->photos->removeElement($photo);
        }
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

    /**
     * @JMSSerializer\VirtualProperty()
     * @JMSSerializer\Groups({"Default", "user_read"})
     * @JMSSerializer\XmlAttribute()
     *
     * @return bool
     */
    public function hasThumbnail()
    {
        return (boolean) ($this->thumbnail
            && ($this->thumbnail->getFile() && $this->thumbnail->getFile() instanceof UploadedFile && $this->thumbnail->getFile()->isValid()
                || $this->thumbnail->getFileName()));
    }

    /**
     * @return string
     */
    public function getFacebookURL()
    {
        return $this->facebookURL;
    }

    /**
     * @param string $facebookURL
     */
    public function setFacebookURL($facebookURL)
    {
        $this->facebookURL = $facebookURL;
    }

    /**
     * @return string
     */
    public function getInstagramURL()
    {
        return $this->instagramURL;
    }

    /**
     * @param string $instagramURL
     */
    public function setInstagramURL($instagramURL)
    {
        $this->instagramURL = $instagramURL;
    }

    /**
     * @return string
     */
    public function getInstagramId()
    {
        return $this->instagramId;
    }

    /**
     * @param string $instagramId
     * @return User
     */
    public function setInstagramId($instagramId)
    {
        $this->instagramId = $instagramId;

        return $this;
    }

    /**
     * @return boolean
     */
    public function isActivated()
    {
        return $this->activated;
    }

    /**
     * @param boolean $activated
     * @return $this
     */
    public function setActivated($activated)
    {
        $this->activated = $activated;

        return $this;
    }

    /**
     * @return string
     */
    public function getActivationToken()
    {
        return $this->activationToken;
    }

    /**
     * @param string $activationToken
     * @return $this
     */
    public function setActivationToken($activationToken)
    {
        $this->activationToken = $activationToken;

        return $this;
    }

    /**
     * @return boolean
     */
    public function isModelNotified()
    {
        return $this->modelNotified;
    }

    /**
     * @param boolean $modelNotified
     */
    public function setModelNotified($modelNotified)
    {
        $this->modelNotified = $modelNotified;
    }

    /**
     * @return bool
     */
    public function needToActivate()
    {
        return (boolean) (!$this->facebookId || !$this->activated);
    }

    public function needToActivateByAdmin()
    {
        return (boolean) ($this->facebookId && !$this->activated);
    }

    public function needToActivateByModel()
    {
        return (boolean) (!$this->facebookId && $this->activated);
    }

    /**
     * @return \DateTime
     */
    public function getLastVisitedDate()
    {
        return $this->lastVisitedDate;
    }

    /**
     * @param \DateTime $lastVisitedDate
     */
    public function setLastVisitedDate($lastVisitedDate)
    {
        $this->lastVisitedDate = $lastVisitedDate;
    }

    /**
     * @return boolean
     */
    public function isOnline()
    {
        return $this->online;
    }

    /**
     * @param boolean $online
     * @return $this
     */
    public function setOnline($online)
    {
        $this->online = (boolean)$online;

        return $this;
    }

    /**
     * @return float
     */
    public function getCoins()
    {
        return $this->coins;
    }

    /**
     * @param float $coins
     */
    public function setCoins($coins)
    {
        $this->coins = $coins;
    }

    /**
     * @JMSSerializer\VirtualProperty()
     * @JMSSerializer\XmlList("group")
     * @JMSSerializer\Groups({"Default", "user_read"})
     */
    public function getGroupNames()
    {
        /** @var Group[] $groups */
        $groups = $this->getGroups();
        $groupNames = [];
        foreach ($groups as $group) {
            $groupNames[] = $group->getName();
        }

        return $groupNames;
    }

    /**
     * @inheritdoc
     * @JMSSerializer\XmlList("role")
     * @JMSSerializer\VirtualProperty()
     * @JMSSerializer\Groups({"Default", "user_read"})
     */
    public function getRoles()
    {
        return parent::getRoles();
    }
}
