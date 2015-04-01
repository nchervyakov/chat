<?php

namespace AppBundle\Controller;

use AppBundle\Entity\User;
use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ModelRequestAdminController extends CRUDController
{
    public function createModelByRequestAction()
    {
        /** @var User $object */
        $object = $this->admin->getSubject();

        if ($object) {
            return $this->redirectToRoute('admin_app_user_create', ['model_request_id' => $object->getId()]);

        } else {
            throw new NotFoundHttpException();
        }
    }
}
