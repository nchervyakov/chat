<?php

namespace AppBundle\Controller;

use AppBundle\Entity\User;
use HWI\Bundle\OAuthBundle\Security\Core\Exception\AccountNotLinkedException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
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

        $userInformation = $this
            ->getResourceOwnerByName($error->getResourceOwnerName())
            ->getUserInformation($error->getRawToken())
        ;

        // enable compatibility with FOSUserBundle 1.3.x and 2.x
        if (interface_exists('FOS\UserBundle\Form\Factory\FactoryInterface')) {
            $form = $this->container->get('hwi_oauth.registration.form.factory')->createForm();
        } else {
            $form = $this->container->get('hwi_oauth.registration.form');
        }

        $formHandler = $this->container->get('hwi_oauth.registration.form.handler');
        if ($formHandler->process($request, $form, $userInformation)) {
            $this->container->get('hwi_oauth.account.connector')->connect($form->getData(), $userInformation);
            /** @var User $user */
            $user = $form->getData();

            if ($user->getGender() == User::GENDER_MALE) {
                $user->addRole(User::ROLE_CLIENT);
                $redirectUrl = $this->generate('search_index');

            } else {
                $user->addRole(User::ROLE_MODEL);
                $redirectUrl = $this->generate('stat_index');
            }

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
}