<?php
namespace AppBundle\Controller;

use AppBundle\Entity\TextMessage;
use AppBundle\Entity\User;
use AppBundle\Exception\ClientNotAgreedToChatException;
use AppBundle\Exception\NotEnoughMoneyException;
use JMS\SecurityExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Class ChatController
 *
 * @package AppBundle\Controller
 * @Route("/chat")
 * @Security("has_role('ROLE_MODEL') or has_role('ROLE_CLIENT')")
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
        $user = $this->getUser();
        $params = ['user' => $user];

        $userRepo = $this->getDoctrine()->getRepository('AppBundle:User');
        $params['companions'] = $userRepo->findUserCompanions($user);

        return $this->render(':Chat:index.html.twig', $params);
    }

    /**
     * @param User $companion
     * @Route("/{companion_id}", name="chat_show", requirements={"companion_id": "\d+"})
     * @ParamConverter("companion", class="AppBundle:User", options={"id": "companion_id"})
     * @return Response
     */
    public function showAction(User $companion)
    {
        if (!$this->get('app.request_access_evaluator')->canChatWith($companion)) {
            throw new AccessDeniedHttpException();
        }

        $user = $this->getUser();
        $params = [
            'user' => $user,
            'companion' => $companion,
            'companions' => [$companion],
            'emoticons' => $this->getDoctrine()->getRepository('AppBundle:Emoticon')->findAllOrdered()
        ];

        $conversation = $this->getDoctrine()->getRepository('AppBundle:Conversation')->getByUsers($user, $companion);
        if ($conversation) {
            $params['conversation'] = $conversation;
            $params['messages'] = $this->getDoctrine()->getRepository('AppBundle:Message')
                ->getConversationLatestMessages($conversation, self::MESSAGES_PER_PAGE);
        }

        $userRepo = $this->getDoctrine()->getRepository('AppBundle:User');
        $params['companions'] = array_merge($params['companions'], $userRepo->findUserCompanions($user, $companion));

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

        /** @var User $user */
        $user = $this->getUser();
        $conversation = $this->getDoctrine()->getRepository('AppBundle:Conversation')
            ->getOrCreateByUsers($user, $companion);

        $content = trim($request->request->get('message'));
        if (!$content) {
            throw new BadRequestHttpException("add_message.empty_message");
        }

        $em = $this->getDoctrine()->getManager();

        $message = new TextMessage($content);
        $message->setAuthor($user);

        try {
            $em->persist($message);
            $this->get('app.conversation')->addConversationMessage($conversation, $message);
            $em->flush();

        } catch (ClientNotAgreedToChatException $ex) {
            if ($request->isXmlHttpRequest()) {
                return new JsonResponse([
                    'error' => true,
                    'message' => $this->get('translator')->trans('chatting.need_to_pay', ['%model%' => $companion->getFullName()]),
                    'need_to_agree_to_pay' => true
                ]);

            } else {
                return $this->redirectToRoute('chat', ['companion_id' => $companion->getId()]);
            }

        } catch (NotEnoughMoneyException $ex) {
            if ($request->isXmlHttpRequest()) {
                return new JsonResponse([
                    'error' => true,
                    'message' => $this->get('translator')->trans('chatting.not_enough_money'),
                    'not_enough_money' => true
                ]);

            } else {
                return $this->redirectToRoute('chat', ['companion_id' => $companion->getId()]);
            }
        }

        if ($request->isXmlHttpRequest()) {
            $responseData = [
                'success' => true,
                'coins' => number_format($user->getCoins(), 2, '.', ''),
                'message' => $this->renderView(':Chat:_message.html.twig', ['message' => $message]),
                'total_time' => $conversation->getSeconds(),
                'stat_html' => $this->renderView(':Chat:_chat_stats.html.twig', [
                    'conversation' => $conversation,
                    'messages' => true
                ]),
                'id' => $message->getId()
            ];

            return new JsonResponse($responseData);

        } else {
            return $this->redirectToRoute('chat', ['companion_id' => $companion->getId()]);
        }
    }

    /**
     * Enables the app to get coins from client for chatting in this chat.
     *
     * @Route("/{companion_id}/agree-to-pay", name="chat_agree_to_pay", methods={"POST"})
     * @ParamConverter("companion", class="AppBundle:User", options={"id": "companion_id"})
     * @Security("has_role('ROLE_CLIENT')")
     * @param User $companion
     * @param Request $request
     * @return JsonResponse|RedirectResponse
     */
    public function agreeToPayAction(User $companion, Request $request)
    {
        if (!$this->get('app.request_access_evaluator')->canChatWith($companion)) {
            throw new AccessDeniedHttpException();
        }

        $user = $this->getUser();
        $conversation = $this->getDoctrine()->getRepository('AppBundle:Conversation')
            ->getOrCreateByUsers($user, $companion);

        $conversation->setClientAgreeToPay(true);
        $this->getDoctrine()->getManager()->flush();

        if ($request->isXmlHttpRequest()) {
            return new JsonResponse(['success' => true]);

        } else {
            return $this->redirectToRoute('chat', ['companion_id' => $companion->getId()]);
        }
    }

    /**
     * @Route("/{companion_id}/new-messages", name="chat_get_new_messages", methods={"GET"})
     * @ParamConverter("companion", class="AppBundle:User", options={"id": "companion_id"})
     * @param User $companion
     * @param Request $request
     * @return Response|JsonResponse
     */
    public function getLatestMessages(User $companion, Request $request)
    {
        if (!$this->get('app.request_access_evaluator')->canChatWith($companion)) {
            throw new AccessDeniedHttpException();
        }

        /** @var User $user */
        $user = $this->getUser();
        $conversationRepo = $this->getDoctrine()->getRepository('AppBundle:Conversation');
        $conversation = $conversationRepo->getByUsers($user, $companion);

        $latestMessageId = $request->query->get('latest_message_id', 0);
        $latestMessages = [];

        if ($conversation) {
            $latestMessages = $this->getDoctrine()->getRepository('AppBundle:Message')
                ->getLatestMessages($conversation, $latestMessageId);

            foreach ($latestMessages as $message) {
                if ($message->getId() > $latestMessageId) {
                    $latestMessageId = $message->getId();
                }
            }
        }

        return new JsonResponse([
            'messages' => $this->renderView(':Chat:_messages.html.twig', ['messages' => $latestMessages]),
            'latestMessageId' => $latestMessageId,
            'coins' => number_format($user->getCoins(), 2, '.', ''),
            'stat_html' => $this->renderView(':Chat:_chat_stats.html.twig', [
                'conversation' => $conversation,
                'messages' => $latestMessages
            ]),
        ]);
    }
}
