<?php

namespace AppBundle\Controller;

use AppBundle\AppBundle;
use AppBundle\Entity\User;
use AppBundle\Event\Event\UserRegisteredEvent;
use AppBundle\Event\Events;
use HWI\Bundle\OAuthBundle\Security\Core\Exception\AccountNotLinkedException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Class ConnectController
 * @package AppBundle\Controller
 */
class ConnectController extends \HWI\Bundle\OAuthBundle\Controller\ConnectController
{
    /**
     * @param Request $request
     * @return RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function connectAction(Request $request)
    {
        $connect = $this->container->getParameter('hwi_oauth.connect');
        $hasUser = $this->container->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_REMEMBERED');

        $error = $this->getErrorForRequest($request);

        // if connecting is enabled and there is no user, redirect to the registration form
        if ($connect
            && !$hasUser
            && $error instanceof AccountNotLinkedException
        ) {
            $key = time();
            $session = $request->getSession();
            $session->set('_hwi_oauth.registration_error.'.$key, $error);

            return new RedirectResponse($this->generateUrl('hwi_oauth_connect_registration', array('key' => $key)));
        }

        if ($error) {
            // TODO: this is a potential security risk (see http://trac.symfony-project.org/ticket/9523)
            $error = $error->getMessage();
        }

        return $this->container->get('templating')->renderResponse('HWIOAuthBundle:Connect:login.html.' . $this->getTemplatingEngine(), array(
            'error'   => $error,
        ));
    }


    /**
     * @param Request $request
     * @param string $key
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws AccessDeniedException
     * @throws \Exception
     * @Route("/login/registration/{key}", name="hwi_oauth_connect_registration")
     */
    public function registrationAction(Request $request, $key)
    {
        $connect = $this->container->getParameter('hwi_oauth.connect');
        if (!$connect) {
            throw new NotFoundHttpException();
        }

        $hasUser = $this->container->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_REMEMBERED');
        if ($hasUser) {
            throw new AccessDeniedException('Cannot connect already registered account.');
        }

        $session = $request->getSession();
        $error = $session->get('_hwi_oauth.registration_error.'.$key);
        $session->remove('_hwi_oauth.registration_error.'.$key);
        $registrationType = $request->getSession()->get('auth.registration_type');

        if (!($error instanceof AccountNotLinkedException) || (time() - $key > 600)) {
            $this->container->get('session')->getFlashBag()->add('warning', 'registration.timeout_message');
            return new RedirectResponse($this->generateUrl('fos_user_security_login'));
            //throw new \Exception('Cannot register an account.');
        }

        $dispatcher = $this->container->get('event_dispatcher');

        $userInformation = $this
            ->getResourceOwnerByName($error->getResourceOwnerName())
            ->getUserInformation($error->getRawToken())
        ;

        $factory = $this->container->get('app.registration.form.factory');
        if ($registrationType === 'model_registration') {
            $factory->setOption('choose_gender', false);
        }

        /** @var Form $form */
        $form = $factory->createForm();

        $formHandler = $this->container->get('hwi_oauth.registration.form.handler');
        if ($formHandler->process($request, $form, $userInformation)) {
            $this->container->get('hwi_oauth.account.connector')->connect($form->getData(), $userInformation);
            /** @var User $user */
            $user = $form->getData();
            $groupManager = $this->container->get('sonata.user.group_manager');

            if ($registrationType === 'model_registration') {
                $modelsGroup = $groupManager->findGroupByName('Models');
                $user->addGroup($modelsGroup);
                $user->setActivated(false);
                $user->setGender(User::GENDER_FEMALE);
                $redirectUrl = $this->generateUrl('homepage');

            } else {
                $clientsGroup = $groupManager->findGroupByName('Clients');
                $user->addGroup($clientsGroup);
                $redirectUrl = $this->generateUrl('search_index');
            }

            $event = new UserRegisteredEvent($user, $userInformation);
            $dispatcher->dispatch(Events::REGISTRATION_SUCCESS, $event);

            $this->container->get('doctrine')->getManager()->flush();

            if ($registrationType === 'model_registration') {
                $this->container->get('session')->getFlashBag()->add('success',
                    $this->container->get('translator')->trans('header.model_registration_success', ['%username%' => $user->getFullName()], 'HWIOAuthBundle'));

            } else {
                // Authenticate the user
                $this->authenticateUser($request, $user, $error->getResourceOwnerName(), $error->getRawToken());

                $this->container->get('session')->getFlashBag()->add('success',
                    $this->container->get('translator')->trans('header.registration_success', ['%username%' => $user->getFullName()], 'HWIOAuthBundle'));
            }

            return new RedirectResponse($redirectUrl);
        }

        // reset the error in the session
        $key = time();
        $session->set('_hwi_oauth.registration_error.'.$key, $error);

        return $this->container->get('templating')->renderResponse('HWIOAuthBundle:Connect:registration.html.' . $this->getTemplatingEngine(), array(
            'key' => $key,
            'form' => $form->createView(),
            'userInformation' => $userInformation,
        ));
    }

    /**
     * @param Request $request
     * @param string $service
     * @return RedirectResponse
     * @Route("/login/{service}", name="oauth_service_redirect")
     * @Route("/connect/{service}", name="oauth_connect_service_redirect")
     */
    public function redirectToServiceAction(Request $request, $service)
    {
        //return parent::redirectToServiceAction($request, $service);
        $authorizationUrl = $this->container->get('hwi_oauth.security.oauth_utils')->getAuthorizationUrl($request, $service);

        if ($service === 'facebook') {
            $state = '';

            if ($activationToken = $request->query->get('activation_token')) {
                /** @var User $model */
                $model = $this->container->get('doctrine')->getRepository('AppBundle:User')->findOneBy(['activationToken' => $activationToken]);
                if (!$model) {
                    throw new NotFoundHttpException();
                }

                if (!$model->needToActivate()) {
                    throw new HttpException(400, 'The model is already activated.');
                }

                $state = http_build_query(['state' => json_encode(['activation_token' => $activationToken])]);

            } else if (($fbRequestType = $request->query->get('type')) && $fbRequestType === 'model_registration') {
                $state = http_build_query(['state' => json_encode(['type' => 'model_registration'])]);
            }

            $authorizationUrl = implode('&', [$authorizationUrl, $state]);
        }

        // Check for a return path and store it before redirect
        if ($request->hasSession()) {
            // initialize the session for preventing SessionUnavailableException
            $session = $request->getSession();
            $session->start();

            $providerKey = $this->container->getParameter('hwi_oauth.firewall_name');
            $sessionKey = '_security.' . $providerKey . '.target_path';

            $param = $this->container->getParameter('hwi_oauth.target_path_parameter');
            if (!empty($param) && $targetUrl = $request->get($param, null, true)) {
                $session->set($sessionKey, $targetUrl);
            }

            if ($this->container->getParameter('hwi_oauth.use_referer') && !$session->has($sessionKey) && ($targetUrl = $request->headers->get('Referer')) && $targetUrl !== $authorizationUrl) {
                $session->set($sessionKey, $targetUrl);
            }
        }

        return new RedirectResponse($authorizationUrl);
    }
}