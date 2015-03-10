<?php
/**
 * Created by IntelliJ IDEA.
 * User: Nikolay Chervyakov 
 * Date: 10.03.2015
 * Time: 11:37
 */


namespace AppBundle\Security;

use AppBundle\Entity\User;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\DependencyInjection\ContainerAware;

/**
 * Class RequestAccessEvaluator
 * @package AppBundle\Security
 */
class RequestAccessEvaluator extends ContainerAware
{
    /**
     * Checks that user can chat with given companion.
     * @param User $companion
     * @DI\SecurityFunction("canChatWith")
     * @return bool
     */
    public function canChatWith(User $companion = null)
    {
        $user = $this->container->get('security.token_storage')->getToken()->getUser();

        if (!$user || !$companion) {
            return false;
        }

        $checker = $this->container->get('security.authorization_checker');
        if ($checker->isGranted('ROLE_ADMIN')) {
            return true;
        }

        if (
            $checker->isGranted(User::ROLE_CLIENT) && !$companion->hasRole(User::ROLE_MODEL)
            || $checker->isGranted(User::ROLE_MODEL) && !$companion->hasRole(User::ROLE_CLIENT)
        ) {
            return false;
        }

        return true;
    }
}