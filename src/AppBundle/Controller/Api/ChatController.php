<?php
/**
 * Created with IntelliJ IDEA by Nick Chervyakov.
 * User: Nikolay Chervyakov 
 * Date: 20.05.2015
 * Time: 13:53
  */



namespace AppBundle\Controller\Api;


use AppBundle\Entity\User;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as FOSRest;
use JMS\Serializer\Annotation\SerializedName;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

/**
 * Class ChatController
 * @package AppBundle\Controller\Api
 *
 * @FOSRest\NamePrefix("api_v1_")
 * @Security("has_role('ROLE_USER')")
 */
class ChatController extends FOSRestController
{
    /**
     * @ApiDoc(
     *      resource=true,
     *      description="Returns a chat.",
     *      section="Chats",
     *      output="AppBundle\Entity\Conversation"
     * )
     *
     * @FOSRest\View()
     * @SerializedName("chat")
     *
     * @param int $id Chat ID
     * @return \AppBundle\Entity\Conversation
     */
    public function getChatAction($id)
    {
        /** @var User $user */
        $user = $this->getUser();
        $conversation = $this->getDoctrine()->getRepository('AppBundle:Conversation')->find($id);
        $accessEvaluator = $this->get('app.request_access_evaluator');

        if (!$conversation || !$accessEvaluator->canChatWith($conversation->getCompanion($user))) {
            throw $this->createNotFoundException('There is no chat with such ID.');
        }

        $view = $this->view($conversation);
        $view->getSerializationContext()->setGroups(['user_read']);

        return $view;
    }

    /**
     * @param int $id
     */
    public function putChatAction($id)
    {

    }

    /**
     * @param int $id
     */
    public function patchChatAction($id)
    {

    }

    /**
     * @param int $id
     */
    public function deleteChatAction($id)
    {

    }
}