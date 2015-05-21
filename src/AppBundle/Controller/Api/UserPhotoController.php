<?php
/**
 * Created with IntelliJ IDEA by Nick Chervyakov.
 * User: Nikolay Chervyakov 
 * Date: 20.05.2015
 * Time: 13:58
  */



namespace AppBundle\Controller\Api;


use FOS\RestBundle\Controller\Annotations as FOSRest;
use FOS\RestBundle\Controller\FOSRestController;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

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
     * @param int $userId User ID
     * @return \AppBundle\Entity\UserPhoto[]|array
     */
    public function getPhotosAction($userId)
    {
        $repo = $this->getDoctrine()->getRepository('AppBundle:UserPhoto');
        return $repo->findBy([], ['name' => 'ASC']);
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
     *      input="AppBundle\Entity\UserPhoto",
     *      output="AppBundle\Entity\UserPhoto",
     *      authentication=true,
     *      authenticationRoles={"ROLE_USER"}
     * )
     *
     * @param $userId
     * @param $id
     * @return \AppBundle\Entity\User|\Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function getPhotoAction($userId, $id)
    {
        $user = $this->getDoctrine()->getRepository('AppBundle:User')->find($id);

        if (!$user) {
            return $this->createNotFoundException("There is no user with such id.");
        }

        return $user;
    }

    /**
     * @ApiDoc(
     *      resource=true,
     *      description="Creates a new user photo",
     *      section="Users",
     *      authentication=true,
     *      authenticationRoles={"ROLE_USER"}
     * )
     *
     * @param int $userId User ID
     */
    public function postPhotoAction($userId)
    {

    }

    /**
     * @ApiDoc(
     *      resource=true,
     *      description="Modifies a user photo",
     *      section="Users",
     *      authentication=true,
     *      authenticationRoles={"ROLE_USER"}
     * )
     *
     * @param int $userId User ID
     * @param int $id Photo ID
     */
    public function putPhotoAction($userId, $id)
    {

    }

    /**
     * @ApiDoc(
     *      resource=true,
     *      description="Modifies certain fields of the user photo",
     *      section="Users",
     *      authentication=true,
     *      authenticationRoles={"ROLE_USER"}
     * )
     *
     * @param int $userId User ID
     * @param int $id Photo ID
     */
    public function patchPhotoAction($userId, $id)
    {

    }
}