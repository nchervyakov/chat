<?php
/**
 * Created with IntelliJ IDEA by Nick Chervyakov.
 * User: Nikolay Chervyakov 
 * Date: 20.05.2015
 * Time: 13:58
  */



namespace AppBundle\Controller\Api;


use AppBundle\Entity\User;
use AppBundle\Event\Event\UserRegisteredEvent;
use AppBundle\Event\Events;
use AppBundle\Model\UserCollection;
use AppBundle\Security\Core\Authentication\Token\ApiAnonymousToken;
use Doctrine\ORM\EntityManager;
use FOS\RestBundle\Controller\Annotations as FOSRest;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Request\ParamFetcherInterface;
use HWI\Bundle\OAuthBundle\OAuth\ResourceOwnerInterface;
use JMS\Serializer\Annotation as JMSSerializer;
use Knp\Bundle\PaginatorBundle\Pagination\SlidingPagination;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Class UserController
 * @package AppBundle\Controller\Api
 * @FOSRest\NamePrefix("api_v1_")
 */
class UserController extends FOSRestController
{
    /**
     * @ApiDoc(
     *      resource=true,
     *      description="Returns a list of users.",
     *      section="Users",
     *      authenticationRoles={"ROLE_ADMIN"},
     *      authentication=true,
     *      statusCodes={
     *          200="Returned when successful"
     *      }
     * )
     *
     * @FOSRest\QueryParam(name="page", requirements="\d+", nullable=true, description="Page from which to list users.")
     * @FOSRest\QueryParam(name="per_page", requirements="\d+", default="10", description="How many users to return per page.")
     *
     * @Security("has_role('ROLE_ADMIN')")
     *
     * @param ParamFetcherInterface $paramFetcher
     * @return \AppBundle\Entity\User[]|array
     */
    public function getUsersAction(ParamFetcherInterface $paramFetcher)
    {
        $page = $paramFetcher->get('page');
        $page = null === $page ? 1 : $page;
        $perPage = $paramFetcher->get('per_page');

        $qb = $this->getDoctrine()->getRepository('AppBundle:User')->createQueryBuilder('u')->orderBy('u.username', 'ASC');

        $paginator = $this->get('knp_paginator');
        /** @var SlidingPagination $pagination */
        $pagination = $paginator->paginate($qb, $page, $perPage);
        $paginationData = $pagination->getPaginationData();
        $result = new UserCollection($pagination->getItems(), $page, $perPage);
        $result->setPageCount($paginationData['pageCount']);

        return $result;
    }

    /**
     * @ApiDoc(
     *      resource=true,
     *      description="Returns a list of models",
     *      section="Users"
     * )
     *
     * @return User[]
     */
    public function getUsersModelsAction()
    {

    }

    /**
     * @ApiDoc(
     *      resource=true,
     *      description="Returns a user by ID.",
     *      section="Users",
     *      statusCodes={
     *          200="Returned when successful",
     *          404="Returned when the user is not found."
     *      },
     *      output="AppBundle\Entity\User",
     *      authentication=true,
     *      authenticationRoles={"ROLE_USER"}
     * )
     *
     * @Security("has_role('ROLE_USER')")
     *
     * @param $id
     * @return \AppBundle\Entity\User|\Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function getUserAction($id)
    {
        $user = $this->getDoctrine()->getRepository('AppBundle:User')->find($id);

        if (!$user) {
            throw $this->createNotFoundException("There is no user with such id.");
        }

        $view = $this->view($user);
        $view->getSerializationContext()->setGroups(['user_read']);

        return $view;
    }

    /**
     * @ApiDoc(
     *      resource=true,
     *      description="Create new user",
     *      section="Users",
     *      output="AppBundle\Entity\User",
     *      authentication=true,
     *      authenticationRoles={"ROLE_API"},
     *      parameters={
     *          {"name"="email", "dataType"="string", "required"=true},
     *          {"name"="firstname", "dataType"="string", "required"=true},
     *          {"name"="lastname", "dataType"="string", "required"=true},
     *          {"name"="gender", "dataType"="choice", "required"=true, "format"="{'m', 'f'}"},
     *          {"name"="type", "dataType"="choice", "required"=true, "format"="{'client', 'model'}"},
     *          {"name"="date_of_birth", "dataType"="date", "required"=true},
     *      }
     * )
     *
     * @FOSRest\View(serializerEnableMaxDepthChecks=true, serializerGroups={"user_read"}, statusCode=201)
     * @FOSRest\RequestParam()
     *
     * @param Request $request
     * @return \FOS\RestBundle\View\View
     * @throws \Exception
     */
    public function postUserAction(Request $request)
    {
        /** @var User|null $authUser */
        $authUser = $this->getUser();

        if ($authUser) {
            throw new AccessDeniedException('You are already registered and cannot register another user.');
        }

        $user = new User();
        $form = $this->get('form.factory')->createNamed('', 'user_registration', $user, [
            'method' => 'POST',
            'api' => true,
            'validation_groups' => ['Default', 'AppRegistration']
        ]);

        $form->handleRequest($request);

        if ($form->isValid()) {
            /** @var ApiAnonymousToken $token */
            $token = $this->get('security.token_storage')->getToken();
            $oauthRequest = $token->getOAuthRequest();

            $registrationType = $form->get('type')->getData();

            $dispatcher = $this->container->get('event_dispatcher');

            $userInformation = $this
                ->getResourceOwnerByName($oauthRequest->getProviderName())
                ->getUserInformation(['access_token' => $oauthRequest->getAccessToken()])
            ;

            /** @var EntityManager $em */
            $em = $this->getDoctrine()->getManager();

            try {
                $em->beginTransaction();

                $this->container->get('hwi_oauth.account.connector')->connect($form->getData(), $userInformation);
                $oauthRequest->setUser($user);

                $groupManager = $this->container->get('sonata.user.group_manager');

                if ($registrationType == 'model') {
                    $modelsGroup = $groupManager->findGroupByName('Models');
                    $user->addGroup($modelsGroup);
                    $user->setActivated(false);

                } else {
                    $clientsGroup = $groupManager->findGroupByName('Clients');
                    $user->addGroup($clientsGroup);
                }

                $event = new UserRegisteredEvent($user, $userInformation);
                $dispatcher->dispatch(Events::REGISTRATION_SUCCESS, $event);

                $em->flush();
                $em->commit();

            } catch (\Exception $ex) {
                $em->rollback();
                throw $ex;
            }

            $view = $this->view($user);
            $view->getSerializationContext()
                ->setGroups(['user_read'])
                ->enableMaxDepthChecks();
            return $view;
        }

        $view = $this->view($form);
        $view->setStatusCode(400);
        return $view;
    }

    /**
     * @ApiDoc(
     *      resource=true,
     *      description="Modifies user with new data.",
     *      section="Users"
     * )
     *
     * @param int $id User ID
     */
    public function putUserAction($id)
    {

    }

    /**
     * @ApiDoc(
     *      resource=true,
     *      description="Modifies some properties of the user",
     *      section="Users"
     * )
     *
     * @param int $id User ID
     */
    public function patchUserAction($id)
    {

    }

//    /**
//     * @ApiDoc(
//     *      resource=true,
//     *      description="Deletes the user",
//     *      section="Users",
//     *      authentication=true,
//     *      authenticationRoles={"ROLE_ADMIN"}
//     * )
//     *
//     * @Security("has_role('ROLE_ADMIN')")
//     *
//     * @param int $id User ID
//     */
//    public function deleteUserAction($id)
//    {
//
//    }

    /**
     * Get a resource owner by name.
     *
     * @param string $name
     *
     * @return ResourceOwnerInterface
     *
     * @throws \RuntimeException if there is no resource owner with the given name.
     */
    protected function getResourceOwnerByName($name)
    {
        $ownerMap = $this->get('hwi_oauth.resource_ownermap.'.$this->container->getParameter('hwi_oauth.firewall_name'));

        if (null === $resourceOwner = $ownerMap->getResourceOwnerByName($name)) {
            throw new \RuntimeException(sprintf("No resource owner with name '%s'.", $name));
        }

        return $resourceOwner;
    }
}