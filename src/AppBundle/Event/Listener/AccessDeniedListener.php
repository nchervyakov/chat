<?php
/**
 * Created with IntelliJ IDEA by Nick Chervyakov.
 * User: Nikolay Chervyakov 
 * Date: 28.05.2015
 * Time: 13:25
  */



namespace AppBundle\Event\Listener;

use AppBundle\Security\Core\Authentication\Token\ApiAnonymousToken;
use AppBundle\Security\Core\Authentication\Token\ApiPreAuthenticatedToken;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\EventListener\ExceptionListener;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class AccessDeniedListener extends ExceptionListener implements ContainerAwareInterface
{
    protected $formats;
    protected $challenge;
    /**
     * @var Container
     */
    protected $container;

    /**
     * Constructor.
     *
     * @param array           $formats    An array with keys corresponding to request formats or content types
     *                                    that must be processed by this listener
     * @param string          $challenge
     * @param string          $controller
     * @param LoggerInterface $logger
     */
    public function __construct($formats, $challenge, $controller, LoggerInterface $logger = null)
    {
        $this->formats = $formats;
        $this->challenge = $challenge;
        parent::__construct($controller, $logger);
    }

    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        static $handling;

        if (true === $handling) {
            return false;
        }

        $request = $event->getRequest();

        if (empty($this->formats[$request->getRequestFormat()]) && empty($this->formats[$request->getContentType()])) {
            return false;
        }

        $handling = true;

        $exception = $event->getException();

        $authToken = $this->container->get('security.token_storage')->getToken();

        if ($authToken instanceof ApiPreAuthenticatedToken) {
            /** @var ApiAnonymousToken|ApiPreAuthenticatedToken $authToken */
            $user = $authToken->getUser();

        } else {
            $user = null;
        }

        if ($exception instanceof AccessDeniedException) {
            if ($user) {
                $exception = new AccessDeniedHttpException('You do not have the necessary permissions', $exception);

            } elseif ($authToken instanceof ApiAnonymousToken) {
                $exception = new AccessDeniedHttpException('You do not have the necessary permissions. Please register in the system.', $exception);

            } else {
                $exception = new UnauthorizedHttpException($this->challenge, 'Please provide valid token.', $exception);
            }

            $event->setException($exception);
            parent::onKernelException($event);

        }

        $handling = false;

        return null;
    }

    /**
     * Sets the Container.
     *
     * @param ContainerInterface|null $container A ContainerInterface instance or null
     *
     * @api
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::EXCEPTION => array('onKernelException', 10),
        );
    }
}