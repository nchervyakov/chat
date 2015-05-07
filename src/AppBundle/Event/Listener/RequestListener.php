<?php
/**
 * Created with IntelliJ IDEA by Nick Chervyakov.
 * User: Nikolay Chervyakov 
 * Date: 09.04.2015
 * Time: 17:30
  */



namespace AppBundle\Event\Listener;


use AppBundle\Entity\User;
use Sonata\UserBundle\Model\UserManagerInterface;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\User\UserInterface;


class RequestListener extends ContainerAware implements EventSubscriberInterface
{
    protected $storage;

    protected $userManager;

    public function __construct(TokenStorage $storage, UserManagerInterface $userManager)
    {
        $this->storage = $storage;
        $this->userManager = $userManager;
    }

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * The array keys are event names and the value can be:
     *
     *  * The method name to call (priority defaults to 0)
     *  * An array composed of the method name to call and the priority
     *  * An array of arrays composed of the method names to call and respective
     *    priorities, or 0 if unset
     *
     * For instance:
     *
     *  * array('eventName' => 'methodName')
     *  * array('eventName' => array('methodName', $priority))
     *  * array('eventName' => array(array('methodName1', $priority), array('methodName2'))
     *
     * @return array The event names to listen to
     *
     * @api
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => 'onKernelRequest',
        ];
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        $token = $this->storage->getToken();

        if ($token && ($user = $token->getUser()) instanceof UserInterface) {
            /** @var User $user */
            $user->setLastVisitedDate(new \DateTime());
            $user->setOnline(true);
            $this->userManager->updateUser($user);

            if (!$event->getRequest()->isXmlHttpRequest()) {
                $this->container->get('app.user_manager')->updateUsersOnlineStatusByProbability();
            }
        }
    }
}