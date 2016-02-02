<?php

namespace PaymentBundle\Entity;

use AppBundle\Entity\User;
use Doctrine\ORM\Mapping as ORM;

/**
 * AbstractOrder
 *
 * @ORM\Table(name="orders")
 * @ORM\Entity(repositoryClass="PaymentBundle\Entity\OrderRepository")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="discriminator", type="string", length=32)
 * @ORM\DiscriminatorMap({
 *      "coin": "PaymentBundle\Entity\CoinOrder"
 * })
 *
 * @ORM\HasLifecycleCallbacks()
 */
abstract class AbstractOrder
{
    const STATUS_NEW = 'new';
    const STATUS_PAYED = 'payed';

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\OneToOne(targetEntity="PaymentBundle\Entity\Payment", inversedBy="order")
     * @ORM\JoinColumn(name="payment_id", referencedColumnName="id", onDelete="SET NULL", nullable=true)
     *
     * @var Payment|null
     */
    protected $payment;

    /**
     * @ORM\Column(name="status", type="string", length=32, options={"default": "new"})
     *
     * @var string
     */
    protected $status = self::STATUS_NEW;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=true)
     *
     * @var User
     */
    protected $user;

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

    public function __construct()
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
     * @return null|Payment
     */
    public function getPayment()
    {
        return $this->payment;
    }

    /**
     * @param null|Payment $payment
     */
    public function setPayment(Payment $payment = null)
    {
        $this->payment = $payment;
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
     * @ORM\PreUpdate()
     */
    public function preUpdate()
    {
        $this->setDateUpdated(new \DateTime());
    }
}
