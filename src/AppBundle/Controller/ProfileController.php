<?php

namespace AppBundle\Controller;

use AppBundle\Entity\User;
use AppBundle\Entity\UserPhoto;
use AppBundle\Form\Type\UserPhotoType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

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
     * @Route("/photos", name="profile_photos")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showAction()
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

            // Pregenerate thumbs
            $vich = $this->get('vich_uploader.templating.helper.uploader_helper');
            $imagine = $this->get('liip_imagine.cache.manager');
            $relativePath = $vich->asset($photo, 'file');
            $imagine->getBrowserPath($relativePath, 'user_photo_thumb');
            $imagine->getBrowserPath($relativePath, 'user_photo_big');
            $imagine->getBrowserPath($relativePath, 'user_message_thumb');

            $this->get('session')->getFlashBag()->add('success', 'profile.photos.successfully_added_photo');
            return $this->redirect($this->generateUrl('profile_photos'));
        }

        return $this->render(':Profile:photos.html.twig', [
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
}
