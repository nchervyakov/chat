<?php
/**
 * Created with IntelliJ IDEA by Nick Chervyakov.
 * User: Nikolay Chervyakov 
 * Date: 20.05.2015
 * Time: 14:00
  */



namespace AppBundle\Controller\Api;


use FOS\RestBundle\Controller\Annotations as FOSRest;
use FOS\RestBundle\Controller\FOSRestController;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

/**
 * Class MessageController
 * @package AppBundle\Controller\Api
 * @FOSRest\NamePrefix("api_v1_")
 */
class MessageController extends FOSRestController
{
    /**
     * @ApiDoc(
     *      resource=true,
     *      description="Returns a chat message collection",
     *      section="Chats",
     *      authenticationRoles={"ROLE_USER"},
     *      authentication="true"
     * )
     *
     * @param $chatId
     * @param int $id Message ID
     */
    public function getMessageAction($chatId, $id)
    {

    }

    /**
     * @ApiDoc(
     *      resource=true,
     *      description="Creates a new message in the chat",
     *      section="Chats"
     * )
     * @param int $chatId Chat ID
     */
    public function postMessageAction($chatId)
    {

    }

//    /**
//     * @ApiDoc(
//     *      resource=true,
//     *      description="Modifies the message in the chat",
//     *      section="Chats"
//     * )
//     *
//     * @param $chatId
//     * @param int $id Message ID
//     */
//    public function putMessageAction($chatId, $id)
//    {
//
//    }

    /**
     * @ApiDoc(
     *      resource=true,
     *      description="Modifies some properties of the message in the chat",
     *      section="Chats"
     * )
     *
     * @param $chatId
     * @param int $id Message ID
     */
    public function patchMessageAction($chatId, $id)
    {

    }

    /**
     * @ApiDoc(
     *      resource=true,
     *      description="Marks the message as deleted by the author",
     *      section="Chats"
     * )
     *
     * @param $chatId
     * @param int $id Message ID
     */
    public function patchMessageDeleteAction($chatId, $id)
    {

    }

    /**
     * @ApiDoc(
     *      resource=true,
     *      description="Creates a message complaint",
     *      section="Chats",
     *      authentication=true,
     *      authenticationRoles={"ROLE_MODEL"}
     * )
     *
     * @Security("has_role('ROLE_MODEL')")
     *
     * @param $chatId
     * @param int $id Message ID
     */
    public function postMessageComplaintAction($chatId, $id)
    {

    }

    /**
     * @ApiDoc(
     *      resource=true,
     *      description="Returns message complaint instance",
     *      section="Chats"
     * )
     *
     * @param int $chatId Chat ID
     * @param int $messageId Message ID
     */
    public function getMessageComplaintAction($chatId, $messageId)
    {

    }
}