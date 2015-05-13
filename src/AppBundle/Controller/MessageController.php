<?php
/**
 * Created with IntelliJ IDEA by Nick Chervyakov.
 * User: Nikolay Chervyakov 
 * Date: 08.05.2015
 * Time: 11:52
  */



namespace AppBundle\Controller;


use AppBundle\Entity\ParticipantMessage;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class MessageController
 * @package AppBundle\Controller
 * @Route("/message")
 * @Security("has_role('ROLE_USER')")
 */
class MessageController extends Controller
{
    /**
     * @Route("/{message_id}/complain", name="complain_message")
     * @ParamConverter("message", class="AppBundle:ParticipantMessage", options={"id": "message_id"})
     * @Security("has_role('ROLE_MODEL')")
     * @param ParticipantMessage $message
     * @return JsonResponse
     */
    public function complainAction(ParticipantMessage $message)
    {
        if ($message->getComplaint()) {
            throw new BadRequestHttpException("The message has already been complained.");
        }

        $this->container->get('app.conversation')->complainMessage($message);

        return new JsonResponse([
            'success' => true,
            'html' => $this->get('templating')->render(':Chat:_message.html.twig', [
                'message' => $message
            ])
        ]);
    }

    /**
     * @Route("/{message_id}/delete", name="delete_own_message")
     * @ParamConverter("message", class="AppBundle:ParticipantMessage", options={"id": "message_id"})
     * @param ParticipantMessage $message
     * @return JsonResponse
     */
    public function deleteMessage(ParticipantMessage $message)
    {
        if ($message->getAuthor()->getId() !== $this->getUser()->getId()) {
            throw new NotFoundHttpException();
        }

        if ($message->isDeletedByUser()) {
            throw new BadRequestHttpException("The message has already been deleted.");
        }

        $this->container->get('app.conversation')->markMessageDeletedByUser($message);

        return new JsonResponse([
            'success' => true,
            'html' => $this->get('templating')->render(':Chat:_message.html.twig', [
                'message' => $message
            ])
        ]);
    }
}