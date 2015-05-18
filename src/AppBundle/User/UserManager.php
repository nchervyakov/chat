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
use Doctrine\ORM\EntityManager;
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

    /**
     * @param User $user
     * @param $profilePicture
     * @return null|UserPhoto
     */
    public function downloadProfilePicture(User $user, $profilePicture)
    {
        try {
            $targetDir = $this->container->getParameter('kernel.root_dir') . '/../web/uploads/photo/profile';
            $fileSystem = $this->container->get('filesystem');
            if (!$fileSystem->exists($targetDir) || !is_dir($targetDir)) {
                $fileSystem->mkdir($targetDir);
            }

            $urlParts = parse_url($profilePicture, PHP_URL_PATH);
            $fileName = basename($urlParts);

            $imagePath = $this->container->get('app.downloader')->download($profilePicture,
                $targetDir . '/' . $fileName);

            if ($imagePath) {
                return $this->addPhotoFromPath($user, $imagePath, $fileName);
            }

        } catch (\Exception $e) {
            $this->container->get('logger')->error('Cannot load FB photo "' . $profilePicture . '"', [
                'exception' => $e,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }

        return null;
    }

    public function addPhotoFromPath(User $user, $imagePath, $fileName)
    {
        $em = $this->container->get('doctrine')->getManager();
        $userPhoto = new UserPhoto();
        $uploadedFile = new UploadedFile($imagePath, $fileName, null, null, null, true);
        $userPhoto->setFile($uploadedFile);
        $this->container->get('vich_uploader.upload_handler')->upload($userPhoto, 'file');

        $em->persist($userPhoto);
        $user->addPhoto($userPhoto);
        $em->flush();

        $this->pregeneratePhotoThumbs($userPhoto);
        return $userPhoto;
    }

    public function updateUsersOnlineStatusByProbability()
    {
        mt_srand();

        $updateProbability = $this->container->getParameter('user.online_status.update_probability');
        $updateProbability = $updateProbability ? (float)$updateProbability : 0.01;
        $updateProbability *= (float)mt_getrandmax();

        $probability = mt_rand();

        if ($probability > $updateProbability) {
            return;
        }

        $this->updateUsersOnlineStatus();
    }

    public function updateUsersOnlineStatus()
    {
        $thresholdDate = new \DateTime();
        $thresholdDate->modify('-15 minutes');

        /** @var User[] $newOfflineUsers */
        $newOfflineUsers = $this->getManager()
            ->createQuery("SELECT u FROM AppBundle\\Entity\\User u WHERE (u.lastVisitedDate IS NULL OR u.lastVisitedDate < :last_visit_date) AND u.online = TRUE")
            ->setParameter('last_visit_date', $thresholdDate)
            ->execute();

//        $this->getManager()->createQuery("UPDATE AppBundle\\Entity\\User u "
//                . "SET u.online = FALSE WHERE (u.lastVisitedDate IS NULL OR u.lastVisitedDate < :last_visit_date) AND u.online = TRUE")
//            ->setParameter('last_visit_date', $thresholdDate)
//            ->execute();

        $notificator = $this->container->get('app.mq_notificator');

        foreach ($newOfflineUsers as $user) {
            $user->setOnline(false);
            $notificator->notifyCompanionsThatUserStatusChanged($user, false);
        }

        $this->getManager()->flush();
    }

    /**
     * @return int
     */
    public function getOnlineUsersCount()
    {
        $thresholdDate = new \DateTime();
        $thresholdDate->modify('-15 minutes');

        $res = $this->getManager()->createQuery("SELECT COUNT(u) cnt FROM AppBundle:User u "
                . "WHERE (u.lastVisitedDate IS NULL OR u.lastVisitedDate < :last_visit_date) AND u.online = TRUE")
            ->setParameter('last_visit_date', $thresholdDate)
            ->execute();

        return (int) $res[0]['cnt'];
    }

    /**
     * @return \Doctrine\Common\Persistence\ObjectManager|object|EntityManager
     */
    public function getManager()
    {
        return $this->container->get('doctrine')->getManager();
    }
}