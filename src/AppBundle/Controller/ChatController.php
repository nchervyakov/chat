<?php
namespace AppBundle\Controller;

use AppBundle\Entity\TextMessage;
use AppBundle\Entity\User;
use JMS\SecurityExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Class ChatController
 * @package AppBundle\Controller
 * @Route("/chat")
 * @Security("has_role('ROLE_USER')")
 */
class ChatController extends Controller
{
    const MESSAGES_PER_PAGE = 10;

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     * @Route("/", name="chat", defaults={"_action": ""})
     */
    public function indexAction()
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $params = ['user' => $user];

        $userRepo = $this->getDoctrine()->getRepository('AppBundle:User');
        $params['friends'] = $userRepo->findUserFriends($user);

        return $this->render(':Chat:index.html.twig', $params);
    }

    /**
     * @param User $companion
     * @Route("/{companion_id}", name="chat_show")
     * @ParamConverter("companion", class="AppBundle:User", options={"id": "companion_id"})
     * @return Response
     */
    public function showAction(User $companion)
    {
        if (!$this->get('app.request_access_evaluator')->canChatWith($companion)) {
            throw new AccessDeniedHttpException();
        }

        $user = $this->get('security.token_storage')->getToken()->getUser();
        $params = [
            'user' => $user,
            'companion' => $companion,
            'friends' => [$companion]
        ];

        $conversation = $this->getDoctrine()->getRepository('AppBundle:Conversation')->getByUsers($user, $companion);
        if ($conversation) {
            $params['conversation'] = $conversation;
            $params['messages'] = $this->getDoctrine()->getRepository('AppBundle:Message')
                ->getConversationLatestMessages($conversation, self::MESSAGES_PER_PAGE);
        }

        $userRepo = $this->getDoctrine()->getRepository('AppBundle:User');
        $params['friends'] = array_merge($params['friends'], $userRepo->findUserFriends($user, $companion));

        return $this->render(':Chat:index.html.twig', $params);
    }

    /**
     * @param User $companion
     * @param Request $request
     * @Route("/{companion_id}/add-message", name="chat_add_message", methods={"POST"})
     * @ParamConverter("companion", class="AppBundle:User", options={"id": "companion_id"})
     * @return Response|JsonResponse
     */
    public function addMessageAction(User $companion, Request $request)
    {
        if (!$this->get('app.request_access_evaluator')->canChatWith($companion)) {
            throw new AccessDeniedHttpException();
        }

        $user = $this->get('security.token_storage')->getToken()->getUser();
        $conversation = $this->getDoctrine()->getRepository('AppBundle:Conversation')
            ->getOrCreateByUsers($user, $companion);

        $content = trim($request->request->get('message'));
        if (!$content) {
            throw new BadRequestHttpException("add_message.empty_message");
        }

        $em = $this->getDoctrine()->getEntityManager();

        $message = new TextMessage($content);
        $message->setAuthor($user);
        $em->persist($message);
        $this->get('app.conversation')->addConversationMessage($conversation, $message);
        $em->flush();

        if ($request->isXmlHttpRequest()) {
            return new JsonResponse([
                'message' => $this->renderView(':Chat:_message.html.twig', ['message' => $message])
            ]);
        } else {
            return $this->redirectToRoute('chat', ['companion_id' => $companion->getId()]);
        }
    }

    protected function getDummyMessage()
    {
        $message = new TextMessage();
        $author = new User();
        $message->setAuthor($author);
    }
}
