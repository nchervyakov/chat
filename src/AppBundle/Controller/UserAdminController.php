<?php
/**
 * Created with IntelliJ IDEA by Nick Chervyakov.
 * User: Nikolay Chervyakov 
 * Date: 30.03.2015
 * Time: 16:46
  */



namespace AppBundle\Controller;


use AppBundle\Entity\User;
use Sonata\AdminBundle\Controller\CRUDController;
use Sonata\AdminBundle\Exception\ModelManagerException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class UserAdminController extends CRUDController
{
    /**
     * @inheritdoc
     */
    public function createAction(Request $request = null)
    {
        // the key used to lookup the template
        $templateKey = 'edit';

        if (false === $this->admin->isGranted('CREATE')) {
            throw new AccessDeniedException();
        }

        /** @var User $object */
        $object = $this->admin->getNewInstance();

        if (($modelRequestId = $request->query->get('model_request_id')) && is_numeric($modelRequestId)) {
            $modelRequest = $this->getDoctrine()->getRepository('AppBundle:ModelRequest')->find($modelRequestId);

            if (!$modelRequest) {
                throw new  NotFoundHttpException("Model request with id=$modelRequestId is missing.");
            }

            $object->setFirstname($modelRequest->getFirstName());
            $object->setLastname($modelRequest->getLastName());
            $object->setEmail($modelRequest->getEmail());
            $object->setUsername($modelRequest->getEmail());
            $object->setFacebookURL($modelRequest->getFacebookURL());
            $object->setInstagramURL($modelRequest->getInstagramURL());
            $object->setModelRequest($modelRequest);
            $this->adjustModel($object);

        } else if ($request->query->get('type') === 'model') {
            $this->adjustModel($object);
        }

        $this->admin->setSubject($object);

        /** @var $form \Symfony\Component\Form\Form */
        $form = $this->admin->getForm();
        $form->setData($object);

        if ($this->getRestMethod($request) == 'POST') {
            $form->submit($request);

            $isFormValid = $form->isValid();

            // persist if the form was valid and if in preview mode the preview was approved
            if ($isFormValid && (!$this->isInPreviewMode($request) || $this->isPreviewApproved($request))) {

                if (false === $this->admin->isGranted('CREATE', $object)) {
                    throw new AccessDeniedException();
                }

                try {
                    $object = $this->admin->create($object);

                    if ($this->isXmlHttpRequest($request)) {
                        return $this->renderJson(array(
                            'result' => 'ok',
                            'objectId' => $this->admin->getNormalizedIdentifier($object)
                        ), 200, array(), $request);
                    }

                    $this->addFlash(
                        'sonata_flash_success',
                        $this->admin->trans(
                            'flash_create_success',
                            array('%name%' => $this->escapeHtml($this->admin->toString($object))),
                            'SonataAdminBundle'
                        )
                    );

                    // redirect to edit mode
                    return $this->redirectTo($object, $request);

                } catch (ModelManagerException $e) {
                    $this->handleModelManagerException($e);

                    $isFormValid = false;
                }
            }

            // show an error message if the form failed validation
            if (!$isFormValid) {
                if (!$this->isXmlHttpRequest($request)) {
                    $this->addFlash(
                        'sonata_flash_error',
                        $this->admin->trans(
                            'flash_create_error',
                            array('%name%' => $this->escapeHtml($this->admin->toString($object))),
                            'SonataAdminBundle'
                        )
                    );
                }
            } elseif ($this->isPreviewRequested($request)) {
                // pick the preview template if the form was valid and preview was requested
                $templateKey = 'preview';
                $this->admin->getShow();
            }
        }

        $view = $form->createView();

        // set the theme for the current Admin Form
        $this->get('twig')->getExtension('form')->renderer->setTheme($view, $this->admin->getFormTheme());

        return $this->render($this->admin->getTemplate($templateKey), array(
            'action' => 'create',
            'form'   => $view,
            'object' => $object,
        ), null, $request);
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function sendModelNotificationAction(Request $request)
    {
        $id = $request->get($this->admin->getIdParameter());
        /** @var User $object */
        $object = $this->admin->getObject($id);

        if (!$object) {
            throw new NotFoundHttpException(sprintf('unable to find the object with id : %s', $id));
        }

        if (false === $this->admin->isGranted('EDIT', $object)) {
            throw new AccessDeniedException();
        }

        if ($object->isActivated()) {
            throw new AccessDeniedHttpException("Model is already activated.");
        }

        $this->admin->setSubject($object);

        if (!$object->getActivationToken()) {
            $object->setActivationToken(sha1($object->getId().'_'.time()));
            $this->getDoctrine()->getManager()->flush();
        }

        $translator = $this->get('translator');
        $message = \Swift_Message::newInstance()
            ->setSubject($translator->trans('model_notification.email_title', [
                '%website%' => $this->container->getParameter('website_robot_email')
            ]))
            ->setTo($object->getEmail())
            ->setFrom($this->container->getParameter('website_robot_email'))
            ->setBody(
                $this->renderView(':User:email_model_notification.html.twig', [
                    'user' => $object,
                    'website' => $this->container->getParameter('website')
                ]),
                'text/html',
                'utf-8'
            );

        $this->get('mailer')->send($message);

        $this->addFlash(
            'sonata_flash_success',
            $this->admin->trans(
                'model_notification.flash_successfully_sent', [], 'SonataUserBundle'
            )
        );

        return $this->redirectTo($object);
    }

    /**
     * Sets up model's roles and groups
     *
     * @param User $model
     * @return User
     */
    protected function adjustModel(User $model)
    {
        $model->setGender(User::GENDER_FEMALE);
        $model->addRole('ROLE_MODEL');
        $model->setActivated(false);

        $modelGroup = $this->getDoctrine()->getRepository('AppBundle:Group')->findOneBy(['name' => 'Models']);
        if ($modelGroup) {
            $model->addGroup($modelGroup);
        }

        return $model;
    }
}