<?php
/**
 * Created with IntelliJ IDEA by Nick Chervyakov.
 * User: Nikolay Chervyakov 
 * Date: 18.05.2015
 * Time: 17:03
  */



namespace AppBundle\Notification;


use AppBundle\Entity\User;
use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\ContainerAware;

/**
 * Class MQNotificator
 * @package AppBundle\Notification
 */
class MQNotificator extends ContainerAware
{
    /**
     * @param User $user
     * @param $isOnline
     */
    public function notifyCompanionsThatUserStatusChanged(User $user, $isOnline)
    {
        /** @var EntityManager $em */
        $em = $this->container->get('doctrine')->getManager();

        $producer = $this->container->get('old_sound_rabbit_mq.notifications_producer');
        $producer->setContentType('application/json');

        $companions = $em->getRepository('AppBundle:User')->findUserCompanions($user, null, false, true);

        foreach ($companions as $companion) {
            $producer->publish(json_encode(['type' => 'user-online-status-changed', 'data' => [
                'user_id' => $user->getId(),
                'is_online' => $isOnline
            ]]), 'user.' . $companion->getId());
        }
    }

    /**
     * @param User $user
     */
    public function notifyUserToken(User $user)
    {
        $socketIOToken = $this->container->get('app.socket_io.token_storage')->getToken();
        $producer = $this->container->get('old_sound_rabbit_mq.user_info_producer');
        $producer->setContentType('application/json');

        $producer->publish(json_encode([
            'user_id' => $user->getId(),
            'token' => $socketIOToken
        ]));
    }
}