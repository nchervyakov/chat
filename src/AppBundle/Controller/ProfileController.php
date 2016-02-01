<?php

namespace AppBundle\Controller;

use AppBundle\Entity\User;
use AppBundle\Entity\UserPhoto;
use AppBundle\Form\Type\UserPhotoType;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManager;
use FOS\UserBundle\Model\UserInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Class ProfileController
 * @package AppBundle\Controller
 *
 * @Route("/profile")
 * @Security("has_role('ROLE_USER')")
 */
class ProfileController extends Controller
{
    /**
     * @Route("/", name="profile_show")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showAction()
    {
        $user = $this->getUser();
        if (!is_object($user) || !$user instanceof UserInterface) {
            throw new AccessDeniedException('This user does not have access to this section.');
        }

        return $this->render('FOSUserBundle:Profile:show.html.twig', array(
            'user' => $user,
            'deleteForm' => $this->createDeleteProfileForm()->createView()
        ));
    }

    /**
     * @Route("/photos", name="profile_photos")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showPhotosAction()
    {
        if (!$this->get('security.authorization_checker')->isGranted('ROLE_MODEL')) {
            throw new NotFoundHttpException();
        }

        $newPhoto = new UserPhoto();
        $form = $this->createPhotoForm($newPhoto);

        return $this->render(':Profile:photos.html.twig', [
            'form' => $form->createView(),
            'user' => $this->getUser()
        ]);
    }

    /**
     * @Route("/add-photo", name="profile_add_photo", methods={"POST"})
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function addPhotoAction(Request $request)
    {
        if (!$this->get('security.authorization_checker')->isGranted('ROLE_MODEL')) {
            throw new NotFoundHttpException();
        }

        $photo = new UserPhoto();
        $form = $this->createPhotoForm($photo);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var User $user */
            $user = $this->getUser();

            $em = $this->getDoctrine()->getManager();
            $em->persist($photo);
            $photo->setOwner($user);
            $user->addPhoto($photo);
            $em->flush();

            $this->get('app.image')->fixOrientation($photo->getFile());

            // Pregenerate thumbs
            $this->get('app.user_manager')->pregeneratePhotoThumbs($photo);

            $this->get('session')->getFlashBag()->add('success', 'profile.photos.successfully_added_photo');
            return $this->redirect($this->generateUrl('profile_photos'));
        }

        return $this->render(':Profile:photos.html.twig', [
            'form' => $form->createView(),
            'user' => $this->getUser()
        ]);
    }

    /**
     * @Route("/delete-photo/{photo}", name="profile_delete_photo", methods={"POST"})
     * @param UserPhoto $photo
     * @return JsonResponse
     */
    public function deletePhotoAction(UserPhoto $photo)
    {
        /** @var User $user */
        $user = $this->getUser();
        if ($photo->getOwner()->getId() !== $user->getId()) {
            throw new AccessDeniedException;
        }

        $em = $this->getDoctrine()->getManager();
        $user->removePhoto($photo);
        $em->remove($photo);
        $em->flush();

        return new JsonResponse(['success' => true]);
    }

    /**
     * @Route("/delete", name="profile_delete")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Exception
     */
    public function deleteAction(Request $request)
    {
        $user = $this->getUser();
        $form = $this->createDeleteProfileForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var EntityManager $em */
            $em = $this->getDoctrine()->getManager();

            /** @var Connection $conn */
            $conn = $this->getDoctrine()->getConnection();

            try {
                $conn->beginTransaction();
                $em->createQuery('DELETE FROM AppBundle:Conversation c WHERE c.model = :user OR c.client = :user')
                    ->execute(['user' => $user]);
                $em->remove($user);
                $em->flush();

                $conn->commit();

            } catch (\Exception $e) {
                $conn->rollBack();
                throw $e;
                //throw new HttpException(400, "Error while deleting user.");
            }

            $this->get('session')->invalidate();
            $this->get('security.token_storage')->setToken(null);

            return $this->redirectToRoute('homepage');
        }

        return $this->render(':Profile:confirm_delete.html.twig', [
            'form' => $form->createView(),
            'user' => $this->getUser()
        ]);
    }

    protected function createPhotoForm(UserPhoto $photo)
    {
        $form = $this->createForm(new UserPhotoType(), $photo, [
            'method' => 'POST',
            'action' => $this->generateUrl('profile_add_photo')
        ]);

        $form->add('add', 'submit', ['label' => 'profile.photos.add']);

        return $form;
    }

    protected function createDeleteProfileForm()
    {
        $form = $this->createFormBuilder(null, [
            'method' => 'POST',
            'action' => $this->generateUrl('profile_delete')
        ])
            ->add('submit', 'submit', [
                'label' => 'profile.delete_confirm_button',
                'translation_domain' => 'messages'
            ])
            ->getForm();

        return $form;
    }
}
