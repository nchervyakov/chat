<?php
/**
 * Created with IntelliJ IDEA by Nick Chervyakov.
 * User: Nikolay Chervyakov
 * Date: 29.01.2016
 * Time: 16:00
 */


namespace PaymentBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Payum\Core\Model\Token;

/**
 * Class PaymentToken
 * @package PaymentBundle\Entity
 *
 * @ORM\Entity()
 * @ORM\Table(name="payment_tokens")
 */
class PaymentToken extends Token
{

}