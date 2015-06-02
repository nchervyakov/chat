<?php
/**
 * Created with IntelliJ IDEA by Nick Chervyakov.
 * User: Nikolay Chervyakov 
 * Date: 13.04.2015
 * Time: 13:59
  */



namespace AppBundle\Exception;


class NotEnoughMoneyException extends \Exception
{
    protected $message = 'There is no enough money to be able to send messages.';
}