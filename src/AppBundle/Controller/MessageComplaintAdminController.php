<?php
/**
 * Created with IntelliJ IDEA by Nick Chervyakov.
 * User: Nikolay Chervyakov 
 * Date: 12.05.2015
 * Time: 16:13
  */



namespace AppBundle\Controller;


use AppBundle\Entity\MessageComplaint;
use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class MessageComplaintAdminController extends CRUDController
{
    public function acceptAction(Request $request)
    {
        /** @var MessageComplaint $complaint */
        $complaint = $this->admin->getSubject();

        if ($complaint) {
            if ($complaint->getStatus() != MessageComplaint::STATUS_REJECTED) {
                $this->get('app.complaints')->acceptComplaint($complaint);

                $this->addFlash(
                    'sonata_flash_success',
                    $this->admin->trans('message_complaint.flash_accepted')
                );

                return new RedirectResponse($request->server->get('HTTP_REFERER'));

            } else {
                $this->addFlash(
                    'sonata_flash_error',
                    $this->admin->trans('message_complaint.flash_complaint_is_rejected')
                );
                throw new AccessDeniedHttpException;
            }

        } else {
            throw new NotFoundHttpException();
        }
    }

    public function rejectAction(Request $request)
    {
        /** @var MessageComplaint $complaint */
        $complaint = $this->admin->getSubject();

        if ($complaint) {
            if ($complaint->getStatus() != MessageComplaint::STATUS_ACCEPTED) {
                $this->get('app.complaints')->rejectComplaint($complaint);
                $this->addFlash(
                    'sonata_flash_success',
                    $this->admin->trans('message_complaint.flash_rejected')
                );

                return new RedirectResponse($request->server->get('HTTP_REFERER'));

            } else {
                $this->addFlash(
                    'sonata_flash_error',
                    $this->admin->trans('message_complaint.flash_complaint_is_accepted')
                );
                throw new AccessDeniedHttpException;
            }

        } else {
            throw new NotFoundHttpException();
        }
    }
}