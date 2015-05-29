<?php
/**
 * Created with IntelliJ IDEA by Nick Chervyakov.
 * User: Nikolay Chervyakov 
 * Date: 26.05.2015
 * Time: 16:39
  */



namespace AppBundle\Entity;


use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation as JMSSerializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity()
 * @ORM\Table(name="oauth_request",
 *      indexes={
 *          @ORM\Index(columns={"token"}, name="token"),
 *          @ORM\Index(columns={"oauth_user_id"}, name="oauth_user_id"),
 *      }
 * )
 * @ORM\HasLifecycleCallbacks()
 *
 * @JMSSerializer\ExclusionPolicy("ALL")
 * @JMSSerializer\XmlRoot("oauth_request")
 */
class OAuthRequest
{
    /**
     * @var int
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(name="id", type="integer")
     *
     * @JMSSerializer\XmlAttribute()
     * @JMSSerializer\Expose()
     * @JMSSerializer\Groups({"Default", "user_read"})
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(name="token", type="string", length=255, nullable=true)
     *
     * @JMSSerializer\XmlAttribute()
     * @JMSSerializer\Expose()
     * @JMSSerializer\Groups({"Default", "user_read"})
     */
    private $token;

    /**
     * @var string
     * @ORM\Column(name="oauth_user_id", type="string", length=255, nullable=true)
     *
     * @JMSSerializer\XmlAttribute()
     * @JMSSerializer\Expose()
     * @JMSSerializer\Groups({"Default", "user_read"})
     */
    private $oauthUserId;

    /**
     * @var string
     * @ORM\Column(name="provider_name", type="string", length=32)
     *
     * @JMSSerializer\XmlAttribute()
     * @JMSSerializer\Expose()
     * @JMSSerializer\Groups({"Default", "user_read"})
     *
     * @Assert\NotBlank()
     */
    private $providerName;

    /**
     * @var string
     * @ORM\Column(name="code", type="text", nullable=true)
     * @Assert\NotBlank()
     */
    private $code;

    /**
     * @var string
     * @ORM\Column(name="access_token", type="string", length=255, nullable=true)
     * @JMSSerializer\Expose()
     * @JMSSerializer\Groups({"Default", "user_read"})
     */
    private $accessToken;

    /**
     * @var string
     * @ORM\Column(name="refresh_token", type="string", length=255, nullable=true)
     * @JMSSerializer\Groups({"Default", "user_read"})
     */
    private $refreshToken;

    /**
     * @var \DateTime
     * @ORM\Column(name="expires", type="datetime", nullable=true)
     * @JMSSerializer\XmlAttribute()
     * @JMSSerializer\Expose()
     * @JMSSerializer\Groups({"user_read"})
     */
    private $expires;

    /**
     * @var \DateTime
     * @ORM\Column(name="access_token_expires", type="datetime", nullable=true)
     * @JMSSerializer\XmlAttribute()
     * @JMSSerializer\Expose()
     * @JMSSerializer\Groups({"user_read"})
     */
    private $accessTokenExpires;

    /**
     * @var \DateTime
     * @ORM\Column(name="refresh_token_expires", type="datetime", nullable=true)
     * @JMSSerializer\XmlAttribute()
     */
    private $refreshTokenExpires;

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User", inversedBy="oauthRequests")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     *
     * @JMSSerializer\Expose()
     * @JMSSerializer\Groups({"user_read"})
     * @JMSSerializer\MaxDepth(1)
     * @JMSSerializer\Type("AppBundle\Entity\User")
     */
    private $user;

    /**
     * @var string
     * @ORM\Column(name="added_by_ip", type="string", length=45)
     * @Gedmo\IpTraceable(on="create")
     */
    private $addedByIp;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_added", type="datetime", nullable=true)
     * @Gedmo\Timestampable(on="create")
     *
     * @JMSSerializer\XmlAttribute()
     * @JMSSerializer\Expose()
     * @JMSSerializer\Groups({"Default", "user_read"})
     * @JMSSerializer\Type("datetime")
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
     * @ORM\Column(name="redirect_uri", type="string", length=255, nullable=true)
     *
     * @JMSSerializer\Expose()
     * @JMSSerializer\Groups({"Default", "user_read"})
     */
    private $redirectUri;

    /**
     * @var array
     * @ORM\Column(name="oauth_data", type="array")
     *
     * @JMSSerializer\Expose()
     * @JMSSerializer\Groups({"Default", "user_read"})
     * @JMSSerializer\Type("array")
     */
    private $oauthData = [];

    /**
     * OAuthRequest constructor.
     */
    public function __construct()
    {
        $this->dateAdded = new \DateTime();
        $this->expires = new \DateTime('+1 week');
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
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @param string $token
     */
    public function setToken($token)
    {
        $this->token = $token;
    }

    /**
     * @return string
     */
    public function getOauthUserId()
    {
        return $this->oauthUserId;
    }

    /**
     * @param string $oauthUserId
     */
    public function setOauthUserId($oauthUserId)
    {
        $this->oauthUserId = $oauthUserId;
    }

    /**
     * @return string
     */
    public function getProviderName()
    {
        return $this->providerName;
    }

    /**
     * @param string $providerName
     */
    public function setProviderName($providerName)
    {
        $this->providerName = $providerName;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param string $code
     */
    public function setCode($code)
    {
        $this->code = $code;
    }

    /**
     * @return string
     */
    public function getAccessToken()
    {
        return $this->accessToken;
    }

    /**
     * @param string $accessToken
     */
    public function setAccessToken($accessToken)
    {
        $this->accessToken = $accessToken;
    }

    /**
     * @return string
     */
    public function getRefreshToken()
    {
        return $this->refreshToken;
    }

    /**
     * @param string $refreshToken
     */
    public function setRefreshToken($refreshToken)
    {
        $this->refreshToken = $refreshToken;
    }

    /**
     * @return \DateTime
     */
    public function getExpires()
    {
        return $this->expires;
    }

    /**
     * @param \DateTime $expires
     */
    public function setExpires($expires)
    {
        $this->expires = $expires;
    }

    /**
     * @return \DateTime
     */
    public function getAccessTokenExpires()
    {
        return $this->accessTokenExpires;
    }

    /**
     * @param \DateTime $accessTokenExpires
     */
    public function setAccessTokenExpires($accessTokenExpires)
    {
        $this->accessTokenExpires = $accessTokenExpires;
    }

    /**
     * @return \DateTime
     */
    public function getRefreshTokenExpires()
    {
        return $this->refreshTokenExpires;
    }

    /**
     * @param \DateTime $refreshTokenExpires
     */
    public function setRefreshTokenExpires($refreshTokenExpires)
    {
        $this->refreshTokenExpires = $refreshTokenExpires;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param User $user
     */
    public function setUser($user)
    {
        $this->user = $user;
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
    public function getRedirectUri()
    {
        return $this->redirectUri;
    }

    /**
     * @param string $redirectUri
     */
    public function setRedirectUri($redirectUri)
    {
        $this->redirectUri = $redirectUri;
    }

    /**
     * @return array
     */
    public function getOauthData()
    {
        return $this->oauthData;
    }

    /**
     * @param array $oauthData
     */
    public function setOauthData(array $oauthData)
    {
        $this->oauthData = $oauthData;
    }
}