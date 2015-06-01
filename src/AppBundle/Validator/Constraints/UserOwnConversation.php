<?php
/**
 * Created with IntelliJ IDEA by Nick Chervyakov.
 * User: Nikolay Chervyakov 
 * Date: 01.06.2015
 * Time: 18:46
  */



namespace AppBundle\Validator\Constraints;


use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class UserOwnConversation extends Constraint
{
    public $message = 'You cannot create conversations for other users.';

    public function validatedBy()
    {
        return 'user_own_conversation';
    }

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}