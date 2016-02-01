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
     * @ORM\Column(name="amount", type="decimal")
     *
     * @var float
     */
    private $amount = 0.0;

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
}

