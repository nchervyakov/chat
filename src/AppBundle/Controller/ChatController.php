<?php
namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/**
 * Class ChatController
 * @package AppBundle\Controller
 * @Route("/chat")
 * @Security("has_role('ROLE_USER')")
 */
class ChatController extends Controller
{
    /**
     * @Route("/{user_id}", name="chat", defaults={"_action": "", "user_id": ""})
     */
    public function indexAction()
    {
        return $this->render(':Chat:index.html.twig');
    }

    /**
     * @Route("/create", name="chat_create")
     */
    public function createChatAction()
    {

    }
}
