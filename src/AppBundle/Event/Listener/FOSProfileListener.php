<?php
/**
 * Created by IntelliJ IDEA.
 * User: Nikolay Chervyakov 
 * Date: 17.03.2015
 * Time: 12:17
 */


namespace AppBundle\Event\Listener;


use AppBundle\Entity\User;
use FOS\UserBundle\Event\FormEvent;
use Symfony\Component\DependencyInjection\ContainerAware;

class FOSProfileListener extends ContainerAware
{
    /**
     * Cleans
     * @param FormEvent $event
     */
    public function onProfileSuccess(FormEvent $event)
    {
        $form = $event->getForm();
        /** @var User $user */
        $user = $form->getData();

        if ($user->getThumbnail() !== null
            && ($user->getThumbnail()->getFile() === null && !$user->getThumbnail()->getFileName())
        ) {
            $user->setThumbnail(null);
            $this->container->get('doctrine')->getManager()->flush();
        }

        if ($user->getThumbnail()) {
            $this->container->get('doctrine')->getManager()->flush();
            $thumbnail = $user->getThumbnail();
            $path = $this->container->get('vich_uploader.storage')->resolvePath($thumbnail, 'file');

            if ($path && ($path = realpath($path))) {
                $this->container->get('app.image')->fixOrientation($path);
            }
        }
    }
}