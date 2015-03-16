<?php
/**
 * Created by IntelliJ IDEA.
 * User: Nikolay Chervyakov 
 * Date: 16.03.2015
 * Time: 16:58
 */


namespace AppBundle\User;


use AppBundle\Entity\User;
use AppBundle\Entity\UserPhoto;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class UserManager extends ContainerAware
{
    public function pregeneratePhotoThumbs(UserPhoto $photo)
    {
        $vich = $this->container->get('vich_uploader.templating.helper.uploader_helper');
        $imagine = $this->container->get('liip_imagine.cache.manager');
        $relativePath = $vich->asset($photo, 'file');
        $imagine->getBrowserPath($relativePath, 'user_photo_thumb');
        $imagine->getBrowserPath($relativePath, 'user_photo_big');
        $imagine->getBrowserPath($relativePath, 'user_message_thumb');
    }

    public function downloadProfilePicture(User $user, $profilePicture)
    {
        try {
            $targetDir = $this->container->getParameter('kernel.root_dir') . './../web/uploads/photo/profile';
            $fileSystem = $this->container->get('filesystem');
            if (!$fileSystem->exists($targetDir) || !is_dir($targetDir)) {
                $fileSystem->mkdir($targetDir);
            }

            $urlParts = parse_url($profilePicture, PHP_URL_PATH);
            $fileName = basename($urlParts);

            $imagePath = $this->container->get('app.downloader')->download($profilePicture,
                $targetDir . '/' . $fileName);

            if ($imagePath) {
                $em = $this->container->get('doctrine')->getManager();
                $userPhoto = new UserPhoto();
                $uploadedFile = new UploadedFile($imagePath, $fileName, null, null, null, true);
                $userPhoto->setFile($uploadedFile);
                $this->container->get('vich_uploader.upload_handler')->upload($userPhoto, 'file');

                $em->persist($userPhoto);
                $user->setThumbnail($userPhoto->getFileName());
                $em->flush();

                $this->pregeneratePhotoThumbs($userPhoto);
            }

        } catch (\Exception $e) {
        }
    }
}