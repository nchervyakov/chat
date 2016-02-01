<?php

namespace PaymentBundle\Entity;

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
}
