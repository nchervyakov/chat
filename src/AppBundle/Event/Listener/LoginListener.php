<?php
/**
 * Created with IntelliJ IDEA by Nick Chervyakov.
 * User: Nikolay Chervyakov 
 * Date: 01.04.2015
 * Time: 10:59
  */



namespace AppBundle\Event\Listener;


use AppBundle\Entity\User;
use HWI\Bundle\OAuthBundle\Security\Core\Authentication\Token\OAuthToken;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

class LoginListener extends ContainerAware
{
    public function onSuccessLogin(InteractiveLoginEvent $event)
    {
        $request = $event->getRequest();
        $state = $request->query->get('state');

        if (!$state) {
            return;
        }

        $state = json_decode($state, true);

        if (!is_array($state)) {
            return;
        }

//        if (($type = $state['type']) && $type == 'model_registration') {
//
//        }

        if ($state['activation_token']) {
            $activationToken = $state['activation_token'];
            /** @var User $model */
            $model = $this->container->get('doctrine')->getRepository('AppBundle:User')->findOneBy(['activationToken' => $activationToken]);

            if (!$model) {
                $this->logout();

            } else if ($model->isActivated()) {
                return;
            }

            $this->activateModel($model);
            $this->container->get('session')->getFlashBag()->add('success', 'Now you are activated!');
        }
    }

    protected function logout()
    {
        $this->container->get('session')->invalidate();
        $this->container->get('security.token_storage')->setToken(null);
    }

    protected function activateModel(User $model)
    {
        $model->setActivated(true);
        $model->setActivationToken('');
        /** @var OAuthToken $token */
        $this->container->get('doctrine')->getManager()->flush();
    }
}