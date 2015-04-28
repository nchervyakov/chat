<?php
/**
 * Created with IntelliJ IDEA by Nick Chervyakov.
 * User: Nikolay Chervyakov 
 * Date: 27.04.2015
 * Time: 11:58
  */



namespace AppBundle\Controller;


use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class QueueController
 * @package AppBundle\Controller
 * @Route("/queue")
 * @Security("has_role('ROLE_USER')")
 */
class QueueController extends Controller
{
    /**
     * @Route("/fetch-new-messages", name="queue_fetch_new_messages", methods={"GET"})
     */
    public function getMessagesAction()
    {
        $messages = $this->getDoctrine()->getRepository('AppBundle:QueueMessage')->getUserNewMessages($this->getUser());
        $res = new Response($this->get('serializer')->serialize(['messages' => $messages], 'json'), 200, [
            'Content-Type' => 'application/json'
        ]);
        return $res;
    }
}