<?php
/**
 * Created with IntelliJ IDEA by Nick Chervyakov.
 * User: Nikolay Chervyakov 
 * Date: 20.05.2015
 * Time: 13:58
  */



namespace AppBundle\Controller\Api;


use AppBundle\Entity\User;
use AppBundle\Entity\UserPhoto;
use AppBundle\Model\UserPhotoCollection;
use Doctrine\ORM\EntityManager;
use FOS\RestBundle\Controller\Annotations as FOSRest;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Request\ParamFetcherInterface;
use Knp\Bundle\PaginatorBundle\Pagination\SlidingPagination;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class UserPhotoController
 * @package AppBundle\Controller\Api
 * @FOSRest\NamePrefix("api_v1_")
 * @Security("has_role('ROLE_USER')")
 */
class UserPhotoController extends FOSRestController
{
    /**
     * @ApiDoc(
     *      resource=true,
     *      description="Returns user photos",
     *      section="Users",
     *      authentication=true,
     *      authenticationRoles={"ROLE_USER"}
     * )
     *
     * @FOSRest\QueryParam(name="page", requirements="\d+", nullable=true, description="Page from which to list stats.")
     * @FOSRest\QueryParam(name="per_page", requirements="\d+", default="10", description="How many stat items to return per page.")
     *
     * @FOSRest\View(serializerEnableMaxDepthChecks=true, serializerGroups={"user_read"})
     *
     * @param int $userId User ID
     * @param ParamFetcherInterface $paramFetcher
     * @return \FOS\RestBundle\View\View
     */
    public function getPhotosAction($userId, ParamFetcherInterface $paramFetcher)
    {
        $user = $this->getDoctrine()->getRepository('AppBundle:User')->find($userId);

        if (!$user) {
            throw $this->createNotFoundException('User with id "' . $userId . '" is not found.');
        }

        $page = $paramFetcher->get('page');
        $page = null === $page ? 1 : $page;
        $perPage = $paramFetcher->get('per_page');

        $qb = $this->getDoctrine()->getRepository('AppBundle:UserPhoto')->createQueryBuilder('up');
        $qb->where('up.owner = :owner')->setParameter('owner', $user);
        $qb->orderBy('up.dateAdded', 'ASC');

        $paginator = $this->get('knp_paginator');
        /** @var SlidingPagination $pagination */
        $pagination = $paginator->paginate($qb, $page, $perPage);
        $paginationData = $pagination->getPaginationData();
        $result = new UserPhotoCollection($pagination->getItems(), $page, $perPage);
        $result->setPageCount($paginationData['pageCount']);
        $result->setTotalItemsCount($paginationData['totalCount']);

        $view = $this->view($result);
        $view->getSerializationContext()->setGroups(['user_read'])
            ->enableMaxDepthChecks();
        return $view;
    }

    /**
     * @ApiDoc(
     *      resource=true,
     *      description="Returns a user photo by ID.",
     *      section="Users",
     *      statusCodes={
     *          200="Returned when successful",
     *          404="Returned when the user is not found."
     *      },
     *      output="AppBundle\Entity\UserPhoto",
     *      authentication=true,
     *      authenticationRoles={"ROLE_USER"}
     * )
     *
     * @param $userId
     * @param $id
     * @return \FOS\RestBundle\View\View
     */
    public function getPhotoAction($userId, $id)
    {
        $photo = $this->getPhotoForRequest($userId, $id);

        $view = $this->view($photo);
        $view->getSerializationContext()->setGroups(['user_read'])
            ->enableMaxDepthChecks();
        return $view;
    }

    /**
     * @ApiDoc(
     *      resource=true,
     *      description="Creates a new user photo",
     *      section="Users",
     *      authentication=true,
     *      authenticationRoles={"ROLE_USER"},
     *      input={
     *          "class"="AppBundle\Form\Type\UserPhotoType",
     *          "name"="",
     *          "options"={
     *              "method"="POST",
     *              "api"=true
     *          }
     *      },
     *      output={
     *          "class"="AppBundle\Entity\UserPhoto",
     *          "groups"={"user_read"}
     *      }
     * )
     *
     * @param Request $request
     * @param int $userId User ID
     * @return \FOS\RestBundle\View\View
     */
    public function postPhotoAction(Request $request, $userId)
    {
        $user = $this->getDoctrine()->getRepository('AppBundle:User')->find($userId);

        if (!$user) {
            throw $this->createNotFoundException("There is no user with such id.");
        }

        $photo = new UserPhoto();
        $form = $this->get('form.factory')->createNamed('', 'user_photo', $photo, [
            'method' => 'POST',
            'validation_groups' => ['create'],
            'api' => true,
            'allow_extra_fields' => true
        ]);

        $form->handleRequest($request);

        if ($form->isValid()) {
            /** @var EntityManager $em */
            $em = $this->getDoctrine()->getManager();
            $em->persist($photo);
            $photo->setOwner($user);
            $user->addPhoto($photo);
            $em->flush();

            $this->get('app.image')->fixOrientation($photo->getFile());

            // Pregenerate thumbs
            $this->get('app.user_manager')->pregeneratePhotoThumbs($photo);

            $view = $this->view($photo);
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
     *      description="Modifies a user photo",
     *      section="Users",
     *      authentication=true,
     *      authenticationRoles={"ROLE_USER"},
     *      input={
     *          "class"="AppBundle\Form\Type\UserPhotoType",
     *          "name"="",
     *          "options"={
     *              "method" = "PUT",
     *              "api" = true,
     *              "edit" = true,
     *              "allow_extra_fields" = true
     *          }
     *      },
     *      output={
     *          "class"="AppBundle\Entity\UserPhoto",
     *          "groups"={"user_read"}
     *      }
     * )
     *
     * @param Request $request
     * @param int $userId User ID
     * @param int $id Photo ID
     * @return \FOS\RestBundle\View\View
     */
    public function putPhotoAction(Request $request, $userId, $id)
    {
        $photo = $this->getPhotoForRequest($userId, $id);

        $form = $this->get('form.factory')->createNamed('', 'user_photo', $photo, [
            'method' => 'PUT',
            'validation_groups' => ['update'],
            'api' => true,
            'edit' => true,
            'allow_extra_fields' => true
        ]);

        $form->handleRequest($request);

        if ($form->isValid()) {
            /** @var EntityManager $em */
            $em = $this->getDoctrine()->getManager();
            $em->flush();

            $view = $this->view($photo);
            $view->getSerializationContext()
                ->setGroups(['user_read'])
                ->enableMaxDepthChecks();
            return $view;
        }

        $view = $this->view($form);
        $view->setStatusCode(400);
        return $view;
    }

//    /**
//     * @ApiDoc(
//     *      resource=true,
//     *      description="Modifies certain fields of the user photo",
//     *      section="Users",
//     *      authentication=true,
//     *      authenticationRoles={"ROLE_USER"}
//     * )
//     *
//     * @param int $userId User ID
//     * @param int $id Photo ID
//     */
//    public function patchPhotoAction($userId, $id)
//    {
//
//    }

    /**
     * @ApiDoc(
     *      resource=true,
     *      description="Removes user photo",
     *      section="Users",
     *      authentication=true,
     *      authenticationRoles={"ROLE_USER"}
     * )
     *
     * @param int $userId User ID
     * @param int $id Photo ID
     * @return \FOS\RestBundle\View\View
     */
    public function deletePhotoAction($userId, $id)
    {
        $photo = $this->getPhotoForRequest($userId, $id);

        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $em->remove($photo);
        $em->flush();

        return $this->view()->setStatusCode(204);
    }

    /**
     * @param int $userId
     * @param int $id Photo ID
     *
     * @return UserPhoto|null
     */
    protected function getPhotoForRequest($userId, $id)
    {
        $user = $this->getDoctrine()->getRepository('AppBundle:User')->find($userId);

        if (!$user) {
            throw $this->createNotFoundException("There is no user with such id.");
        }

        $qb = $this->getDoctrine()->getRepository('AppBundle:UserPhoto')->createQueryBuilder('up');
        $qb->where('up.owner = :owner')->setParameter('owner', $user);
        $qb->andWhere('up.id = :id')->setParameter('id', $id);

        $result = $qb->getQuery()->setMaxResults(1)->execute();

        if (!$result[0]) {
            throw $this->createNotFoundException("There is no user photo with such id.");
        }

        return $result[0];
    }
}