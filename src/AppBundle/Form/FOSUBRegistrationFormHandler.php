<?php
/**
 * Created by IntelliJ IDEA.
 * User: Nikolay Chervyakov 
 * Date: 16.03.2015
 * Time: 16:15
 */


namespace AppBundle\Form;


use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Security\Core\User\UserInterface;

class FOSUBRegistrationFormHandler extends \HWI\Bundle\OAuthBundle\Form\FOSUBRegistrationFormHandler
{
    protected function setUserInformation(UserInterface $user, UserResponseInterface $userInformation)
    {
        $accessor = PropertyAccess::createPropertyAccessor();
        //$accessor->setValue($user, 'username', $this->getUniqueUserName($userInformation->getNickname()));

        if ($accessor->isWritable($user, 'email')) {
            $accessor->setValue($user, 'email', $userInformation->getEmail());
            $accessor->setValue($user, 'username', $userInformation->getEmail());

            $fullName = $userInformation->getRealName();
            $nameParts = preg_split('/\s+/', $fullName, -1, PREG_SPLIT_NO_EMPTY);
            $accessor->setValue($user, 'firstName', $nameParts[0]);
            $accessor->setValue($user, 'lastName', $nameParts[1]);
        }

        return $user;
    }
}