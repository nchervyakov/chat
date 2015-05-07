<?php
/**
 * Created with IntelliJ IDEA by Nick Chervyakov.
 * User: Nikolay Chervyakov 
 * Date: 15.04.2015
 * Time: 17:24
  */

namespace AppBundle\Command;


use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

/**
 * Class NotifyInactiveClientsCommand
 * @package AppBundle\Command
 */
class NotifyInactiveClientsCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('clients:notify-inactive')
            ->setDescription('Notifies those clients who registered but did not send any message for several days.');
    }
}