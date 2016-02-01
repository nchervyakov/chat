<?php
/**
 * Created with IntelliJ IDEA by Nick Chervyakov.
 * User: Nikolay Chervyakov 
 * Date: 12.05.2015
 * Time: 18:04
  */



namespace AppBundle\Conversation;


use AppBundle\Entity\MessageComplaint;
use AppBundle\Entity\User;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class ComplaintsService extends ContainerAware
{
    public function getOpenComplaintsCount()
    {
        return $this->getDoctrine()->getRepository('AppBundle:MessageComplaint')->getOpenComplaintsCount();
    }

    public function acceptComplaint(MessageComplaint $complaint)
    {
        if ($complaint->getStatus() === MessageComplaint::STATUS_REJECTED) {
            throw new AccessDeniedException;
        }

        $complaint->accept();
        $complaint->setResolvedBy($this->getUser());
        $this->getDoctrine()->getManager()->flush();
    }

    public function rejectComplaint(MessageComplaint $complaint)
    {
        if ($complaint->getStatus() === MessageComplaint::STATUS_ACCEPTED) {
            throw new AccessDeniedException;
        }

        $complaint->reject();
        $complaint->setResolvedBy($this->getUser());
        $this->getDoctrine()->getManager()->flush();
    }

    protected function getDoctrine()
    {
        return $this->container->get('doctrine');
    }

    /**
     * @return mixed|User
     */
    protected function getUser()
    {
        return $this->container->get('security.token_storage')->getToken()->getUser();
    }
}