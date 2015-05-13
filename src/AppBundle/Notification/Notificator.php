<?php
/**
 * Created with IntelliJ IDEA by Nick Chervyakov.
 * User: Nikolay Chervyakov 
 * Date: 06.04.2015
 * Time: 20:44
  */



namespace AppBundle\Notification;


use AppBundle\Entity\Message;
use AppBundle\Entity\User;
use Symfony\Component\DependencyInjection\ContainerAware;

class Notificator extends ContainerAware
{
    public function notifyUserToActivateHimself(User $user)
    {
        if (!$user->getActivationToken()) {
            $user->setActivationToken(sha1($user->getId().'_'.time()));
            $this->container->get('doctrine')->getManager()->flush();
        }

        $translator = $this->container->get('translator');

        $message = \Swift_Message::newInstance()
            ->setSubject($translator->trans('model_notification.email_title', [
                '%website%' => $this->container->getParameter('website')
            ]))
            ->setTo($user->getEmail())
            ->setFrom($this->container->getParameter('website_robot_email'))
            ->setBody(
                $this->container->get('templating')->render(':User:email_model_notification.html.twig', [
                    'user' => $user,
                    'website' => $this->container->getParameter('website')
                ]),
                'text/html',
                'utf-8'
            );

        $this->container->get('mailer')->send($message);
    }

    public function notifyModelSheIsActivatedByAdmin(User $model)
    {
        $translator = $this->container->get('translator');

        $message = \Swift_Message::newInstance()
            ->setSubject($translator->trans('model_notification.activated_by_admin_title', [
                '%website%' => $this->container->getParameter('website')
            ]))
            ->setTo($model->getEmail())
            ->setFrom($this->container->getParameter('website_robot_email'))
            ->setBody(
                $this->container->get('templating')->render(':User:email_model_notification_activated_by_admin.html.twig', [
                    'user' => $model,
                    'website' => $this->container->getParameter('website')
                ]),
                'text/html',
                'utf-8'
            );

        $this->container->get('mailer')->send($message);
    }

    /**
     * @param User $addressee
     * @param Message $message
     */
    public function notifyNewMessageArrived(User $addressee, Message $message)
    {
        $translator = $this->container->get('translator');

        $message = \Swift_Message::newInstance()
            ->setSubject($translator->trans('notification.new_message.subject', [
                '%companion%' => $message->getAuthor()->getFullName(),
                '%website%' => $this->container->getParameter('website')
            ]))
            ->setTo($addressee->getEmail())
            ->setFrom($this->container->getParameter('website_robot_email'))
            ->setBody(
                $this->container->get('templating')->render(':User:email_new_message_notification.html.twig', [
                    'companion' => $message->getAuthor(),
                    'user' => $addressee,
                    'website' => $this->container->getParameter('website')
                ]),
                'text/html',
                'utf-8'
            );

        $this->container->get('mailer')->send($message);
    }
}