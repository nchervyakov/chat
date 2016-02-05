<?php
/**
 * Created with IntelliJ IDEA by Nick Chervyakov.
 * User: Nikolay Chervyakov
 * Date: 05.02.2016
 * Time: 15:36
 */


namespace AppBundle\Security\Http\Authentication;


use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\DefaultAuthenticationSuccessHandler;

class AuthenticationSuccessHandler extends DefaultAuthenticationSuccessHandler implements ContainerAwareInterface
{
    /** @var  Container */
    protected $container;

    public function onAuthenticationSuccess(Request $request, TokenInterface $token)
    {
        $checker = $this->container->get('security.authorization_checker');

        if ($checker->isGranted('ROLE_CLIENT')) {
            return new RedirectResponse( $this->container->get('router')->generate('homepage'));
        }

        return parent::onAuthenticationSuccess($request, $token);
    }

    /**
     * Sets the container.
     *
     * @param ContainerInterface|Container|null $container A ContainerInterface instance or null
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }
}