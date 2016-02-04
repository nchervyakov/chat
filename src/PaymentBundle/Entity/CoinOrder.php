<?php

namespace PaymentBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CoinOrder
 *
 * @ORM\Entity()
 */
class CoinOrder extends AbstractOrder
{
    /**
     * @ORM\Column(name="coins", type="decimal")
     *
     * @var float
     */
    private $coins = 0.0;

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

    public function getDescription()
    {
        return 'Purchase of ' . $this->coins . ' coins';
    }
}

