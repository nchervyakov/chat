<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Emoticon
 *
 * @ORM\Table(name="emoticons",
 *      indexes={
 *          @ORM\Index(columns={"symbol"}, name="symbol")
 *      }
 * )
 * @ORM\Entity(repositoryClass="AppBundle\Entity\EmoticonRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Emoticon
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(name="symbol", type="string", length=32)
     */
    private $symbol;

    /**
     * @var array
     * @ORM\Column(name="aliases", type="array")
     */
    private $aliases = [];

    /**
     * @var string
     * @ORM\Column(name="icon", type="string", length=255)
     */
    private $icon;

    /**
     * @var int
     * @ORM\Column(name="sort_order", type="integer")
     */
    private $sortOrder;

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

    /**
     * Set symbol
     *
     * @param string $symbol
     * @return Emoticon
     */
    public function setSymbol($symbol)
    {
        $this->symbol = $symbol;

        return $this;
    }

    /**
     * Get symbol
     *
     * @return string 
     */
    public function getSymbol()
    {
        return $this->symbol;
    }

    /**
     * Set aliases
     *
     * @param array $aliases
     * @return Emoticon
     */
    public function setAliases(array $aliases)
    {
        $this->aliases = $aliases;

        return $this;
    }

    /**
     * @param string $alias
     * @return $this
     */
    public function addAlias($alias)
    {
        $this->aliases[] = $alias;

        return $this;
    }

    /**
     * Get aliases
     *
     * @return array 
     */
    public function getAliases()
    {
        return $this->aliases;
    }

    /**
     * Set icon
     *
     * @param string $icon
     * @return Emoticon
     */
    public function setIcon($icon)
    {
        $this->icon = $icon;

        return $this;
    }

    /**
     * Get icon
     *
     * @return string 
     */
    public function getIcon()
    {
        return $this->icon;
    }

    /**
     * Set sortOrder
     *
     * @param integer $sortOrder
     * @return Emoticon
     */
    public function setSortOrder($sortOrder)
    {
        $this->sortOrder = $sortOrder;

        return $this;
    }

    /**
     * Get sortOrder
     *
     * @return integer 
     */
    public function getSortOrder()
    {
        return $this->sortOrder;
    }
}
