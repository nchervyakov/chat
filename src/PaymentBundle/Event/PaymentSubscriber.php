<?php
/**
 * Created with IntelliJ IDEA by Nick Chervyakov.
 * User: Nikolay Chervyakov
 * Date: 03.02.2016
 * Time: 12:40
 */


namespace PaymentBundle\Event;


use AppBundle\Entity\User;
use PaymentBundle\PaymentEvents;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PaymentSubscriber extends ContainerAware implements EventSubscriberInterface
{

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
     *  * array('eventName' => array(array('methodName1', $priority), array('methodName2')))
     *
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        return [
            PaymentEvents::ORDER_PAYED => 'onOrderPayed'
        ];
    }

    public function onOrderPayed(OrderPayedEvent $event)
    {
        /** @var User $user */
        $user = $this->container->get('security.token_storage')->getToken()->getUser();

        $this->container->get('app.notificator')->notifyOrderPayed($user, $event->getOrder());
    }
}