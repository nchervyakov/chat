<?php
/**
 * Created by IntelliJ IDEA.
 * User: Nikolay Chervyakov 
 * Date: 10.03.2015
 * Time: 11:26
 */


namespace AppBundle\Conversation;


use AppBundle\Entity\Conversation;
use AppBundle\Entity\Message;
use Symfony\Component\DependencyInjection\ContainerAware;

/**
 * Class ConversationService
 * @package AppBundle\Conversation
 */
class ConversationService extends ContainerAware
{
    public function addConversationMessage(Conversation $conversation, Message $message)
    {
        $conversation->addMessage($message);
        $message->setConversation($conversation);
    }
}