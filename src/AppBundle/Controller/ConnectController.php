<?php

namespace AppBundle\Controller;

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
     * @param string $key
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws AccessDeniedException
     * @throws \Exception
     * @Route("/login/registration/{key}")
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

        if (!($error instanceof AccountNotLinkedException) || (time() - $key > 300)) {
            $this->container->get('session')->getFlashBag()->add('warning', 'registration.timeout_message');
            return new RedirectResponse($this->generate('fos_user_security_login'));
            //throw new \Exception('Cannot register an account.');
        }

        $dispatcher = $this->container->get('event_dispatcher');

        $userInformation = $this
            ->getResourceOwnerByName($error->getResourceOwnerName())
            ->getUserInformation($error->getRawToken())
        ;

        // enable compatibility with FOSUserBundle 1.3.x and 2.x
//        if (interface_exists('FOS\UserBundle\Form\Factory\FactoryInterface')) {
        /** @var Form $form */
        $form = $this->container->get('hwi_oauth.registration.form.factory')->createForm();
//        } else {
//            $form = $this->container->get('hwi_oauth.registration.form');
//        }

        $formHandler = $this->container->get('hwi_oauth.registration.form.handler');
        if ($formHandler->process($request, $form, $userInformation)) {
            $this->container->get('hwi_oauth.account.connector')->connect($form->getData(), $userInformation);
            /** @var User $user */
            $user = $form->getData();

            //if ($user->getGender() == User::GENDER_MALE) {

            $clientsGroup = $this->container->get('doctrine')->getRepository('AppBundle:Group')->findOneBy(['name' => 'Clients']);
            if ($clientsGroup) {
                $user->addGroup($clientsGroup);
            }
            //$user->addRole(User::ROLE_CLIENT);
            $redirectUrl = $this->generate('search_index');

//            } else {
//                $user->addRole(User::ROLE_MODEL);
//                $redirectUrl = $this->generate('stat_index');
//            }

            $event = new UserRegisteredEvent($user, $userInformation);
            $dispatcher->dispatch(Events::REGISTRATION_SUCCESS, $event);

            $this->container->get('doctrine')->getManager()->flush();

            // Authenticate the user
            $this->authenticateUser($request, $user, $error->getResourceOwnerName(), $error->getRawToken());

            $this->container->get('session')->getFlashBag()->add('success',
                $this->container->get('translator')->trans('header.registration_success', ['%username%' => $user->getFullName()], 'HWIOAuthBundle'));

            return new RedirectResponse($redirectUrl);

//            return $this->container->get('templating')->renderResponse('HWIOAuthBundle:Connect:registration_success.html.' . $this->getTemplatingEngine(), array(
//                'userInformation' => $userInformation,
//            ));
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
     */
    public function redirectToServiceAction(Request $request, $service)
    {
        //return parent::redirectToServiceAction($request, $service);
        $authorizationUrl = $this->container->get('hwi_oauth.security.oauth_utils')->getAuthorizationUrl($request, $service);

        if ($service == 'facebook' && $activationToken = $request->query->get('activation_token')) {
            $model = $this->container->get('doctrine')->getRepository('AppBundle:User')->findOneBy(['activationToken' => $activationToken]);
            if (!$model) {
                throw new NotFoundHttpException();
            }

            if ($model->isActivated()) {
                throw new HttpException("The model is already activated.");
            }

            $state = http_build_query(['state' => json_encode(['activation_token' => $activationToken])]);
            $authorizationUrl .= '&'.$state;
        }
         //var_dump($authorizationUrl);exit;
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