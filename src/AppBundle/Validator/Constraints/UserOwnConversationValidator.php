<?php
/**
 * Created with IntelliJ IDEA by Nick Chervyakov.
 * User: Nikolay Chervyakov 
 * Date: 01.06.2015
 * Time: 18:47
  */



namespace AppBundle\Validator\Constraints;


use AppBundle\Entity\Conversation;
use AppBundle\Entity\User;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * Checks that user creates a conversation for himself and not for other users.
 */
class UserOwnConversationValidator extends ConstraintValidator implements ContainerAwareInterface
{
    /**
     * @var Container
     */
    protected $container;

    /**
     * Checks if the passed value is valid.
     *
     * @param mixed|Conversation $conversation Conversation being validated
     * @param Constraint $constraint The constraint for the validation
     * @api
     */
    public function validate($conversation, Constraint $constraint)
    {
        $token = $this->container->get('security.token_storage')->getToken();
        if (!$token || !$token->getUser()) {
            return;
        }

        /** @var ExecutionContextInterface $context */
        $context = $this->context;

        if (!$conversation->getClient() || !$conversation->getModel()) {
            $context->buildViolation("Both users must be provided")->addViolation();
            return;
        }

        /** @var User $user */
        $user = $token->getUser();

        if ($conversation->getClient()->getId() != $user->getId() && $conversation->getModel()->getId() != $user->getId()) {
            $context->buildViolation("You can only create chats for yourself.")->addViolation();
        }

        $existingConversation = $this->container->get('doctrine')->getRepository('AppBundle:Conversation')
            ->getByUsers($conversation->getClient(), $conversation->getModel());

        if ($existingConversation) {
            $context->buildViolation("You already have conversation for you and your companion, with ID: "
                . $existingConversation->getId())->addViolation();
        }
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
}
