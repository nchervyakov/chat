<?php
/**
 * Created with IntelliJ IDEA by Nick Chervyakov.
 * User: Nikolay Chervyakov 
 * Date: 16.04.2015
 * Time: 20:27
  */



namespace AppBundle\Event\Listener;


use Symfony\Component\DependencyInjection\ContainerInterface;

class SessionListener extends \Symfony\Component\HttpKernel\EventListener\SessionListener
{
    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    protected function getSession()
    {
        if (!$this->container->has('session')) {
            return null;
        }

        $session = $this->container->get('session');

        if (($sessId = $_POST[session_name()])) {
            $session->setId($sessId);
        }

        return $session;
    }
}