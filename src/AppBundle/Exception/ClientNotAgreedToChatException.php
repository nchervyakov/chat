<?php
/**
 * Created with IntelliJ IDEA by Nick Chervyakov.
 * User: Nikolay Chervyakov 
 * Date: 13.04.2015
 * Time: 13:43
  */



namespace AppBundle\Exception;


class ClientNotAgreedToChatException extends \Exception
{
    protected $message = 'Client didn\'t agree to pay yet.';
}