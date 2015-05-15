<?php
/**
 * Created with IntelliJ IDEA by Nick Chervyakov.
 * User: Nikolay Chervyakov 
 * Date: 09.04.2015
 * Time: 17:30
  */



namespace AppBundle\Event\Listener;


use AppBundle\Entity\User;
use Doctrine\ORM\EntityManager;
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
        $socketIOToken = '';

        if ($token && ($user = $token->getUser()) instanceof UserInterface) {
            /** @var EntityManager $em */
            $em = $this->container->get('doctrine')->getManager();
            if ($em && $em->isOpen()) {
                /** @var User $user */
                $user->setLastVisitedDate(new \DateTime());
                $user->setOnline(true);
                $this->userManager->updateUser($user);
            }

            if (!$event->getRequest()->isXmlHttpRequest()) {
                $this->container->get('app.user_manager')->updateUsersOnlineStatusByProbability();
            }

            $socketIOToken = $this->container->get('app.socket_io.token_storage')->getToken();
            $producer = $this->container->get('old_sound_rabbit_mq.user_info_producer');
            $producer->setContentType('application/json');
            $producer->publish(json_encode([
                'user_id' => $user->getId(),
                'token' => $socketIOToken
            ]));
        }

        $this->container->get('twig')->addGlobal('socket_io_token', $socketIOToken);
    }
}