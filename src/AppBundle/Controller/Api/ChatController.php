<?php
/**
 * Created with IntelliJ IDEA by Nick Chervyakov.
 * User: Nikolay Chervyakov 
 * Date: 20.05.2015
 * Time: 13:53
  */



namespace AppBundle\Controller\Api;


use AppBundle\Entity\Conversation;
use AppBundle\Entity\User;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as FOSRest;
use JMS\Serializer\Annotation\SerializedName;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;

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
     * @FOSRest\View(serializerEnableMaxDepthChecks=true)
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

        $groups = ['user_read'];
        if ($this->isGranted('ROLE_MODEL')) {
            $groups[] = 'model_read';
        } else if ($this->isGranted('ROLE_CLIENT')) {
            $groups[] = 'client_read';
        }

        $view = $this->view($conversation);
        $view->getSerializationContext()
            ->setGroups($groups)
            ->enableMaxDepthChecks();

        return $view;
    }

    /**
     * @ApiDoc(
     *      resource=true,
     *      description="Creates a new chat",
     *      section="Chats",
     *      authentication=true,
     *      authenticationRoles={"ROLE_USER"},
     *      input={
     *          "class"="AppBundle\Form\Type\ConversationType",
     *          "name"="",
     *          "options"={
     *              "method"="POST",
     *          }
     *      },
     *      output={
     *          "class"="AppBundle\Entity\Conversation",
     *          "groups"={"user_read"}
     *      }
     * )
     *
     * @FOSRest\View(serializerEnableMaxDepthChecks=true)
     *
     * @param Request $request
     * @return \FOS\RestBundle\View\View
     */
    public function postChatAction(Request $request)
    {
        $conversation = new Conversation();

        $form = $this->get('form.factory')->createNamed('', 'conversation', $conversation, [
            'method' => 'POST',
            'validation_groups' => ['create'],
        ]);

        $form->handleRequest($request);

        if ($form->isValid()) {
            /** @var \Doctrine\ORM\EntityManager $em */
            $em = $this->getDoctrine()->getManager();
            $em->persist($conversation);
            $em->flush();

            $view = $this->view($conversation);
            $view->getSerializationContext()
                ->setGroups(['user_read'])
                ->enableMaxDepthChecks();
            return $view;
        }

        $view = $this->view($form);
        $view->setStatusCode(400);
        return $view;
    }
}