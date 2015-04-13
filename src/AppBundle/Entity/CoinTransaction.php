<?php
/**
 * Created with IntelliJ IDEA by Nick Chervyakov.
 * User: Nikolay Chervyakov 
 * Date: 13.04.2015
 * Time: 12:51
  */



namespace AppBundle\Entity;


use Doctrine\ORM\Mapping as ORM;

/**
 * Class CoinTransaction
 * @package AppBundle\Entity
 * @ORM\Entity(repositoryClass="AppBundle\Entity\CoinTransactionRepository")
 * @ORM\Table(name="coin_transactions")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn("discriminator", type="string", length=32)
 * @ORM\DiscriminatorMap({
 *      "basic": "AppBundle\Entity\CoinTransaction"
 * })
 * @ORM\HasLifecycleCallbacks()
 */
class CoinTransaction 
{
    /**
     * @var int
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(name="id", type="integer")
     */
    private $id;

    /**
     * @var User|null
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User")
     * @ORM\JoinColumn(name="source_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     */
    private $source;

    /**
     * @var User|null
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User")
     * @ORM\JoinColumn(name="target_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     */
    private $target;

    /**
     * @var float
     * @ORM\Column(name="coins_amount", type="decimal", precision=10, scale=6, options={"default": 0.0})
     */
    private $amount = 0.0;

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
     * CoinTransaction constructor.
     */
    public function __construct()
    {
        $this->setDateAdded(new \DateTime());
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return User|null
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * @param User|null $source
     */
    public function setSource($source)
    {
        $this->source = $source;
    }

    /**
     * @return User|null
     */
    public function getTarget()
    {
        return $this->target;
    }

    /**
     * @param User|null $target
     */
    public function setTarget($target)
    {
        $this->target = $target;
    }

    /**
     * @return float
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param float $amount
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
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