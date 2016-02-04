<?php
/**
 * Created with IntelliJ IDEA by Nick Chervyakov.
 * User: Nikolay Chervyakov
 * Date: 03.02.2016
 * Time: 12:37
 */


namespace PaymentBundle\Event;


use PaymentBundle\Entity\AbstractOrder;
use PaymentBundle\Entity\CoinOrder;
use PaymentBundle\Entity\Payment;
use Symfony\Component\EventDispatcher\Event;

class OrderPayedEvent extends Event
{
    /**
     * @var AbstractOrder
     */
    protected $order;

    /**
     * @var Payment
     */
    protected $payment;

    public function __construct(AbstractOrder $order, Payment $payment)
    {
        $this->order = $order;
        $this->payment = $payment;
    }

    /**
     * @return AbstractOrder|CoinOrder
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @return Payment
     */
    public function getPayment()
    {
        return $this->payment;
    }
}