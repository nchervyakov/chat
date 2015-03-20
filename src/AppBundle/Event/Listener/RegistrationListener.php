<?php
/**
 * Created by IntelliJ IDEA.
 * User: Nikolay Chervyakov 
 * Date: 20.03.2015
 * Time: 14:35
 */


namespace AppBundle\Event\Listener;


use AppBundle\Entity\User;
use AppBundle\Event\Event\UserRegisteredEvent;
use Guzzle\Common\Exception\RuntimeException;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use Symfony\Component\DependencyInjection\ContainerAware;

class RegistrationListener extends ContainerAware
{
    protected $fbParams;

    /**
     * @var User
     */
    protected $user;

    /**
     * @var UserResponseInterface
     */
    protected $userInformation;

    /**
     * Performs important actions on successful user registration.
     *
     * @param UserRegisteredEvent $event
     */
    public function onRegistrationSuccess(UserRegisteredEvent $event)
    {
        $userInformation = $event->getUserInformation();
        if ($userInformation->getProfilePicture()) {
            $this->container->get('app.user_manager')
                ->downloadProfilePicture($event->getUser(), $userInformation->getProfilePicture());
        }

        $this->user = $event->getUser();
        $this->userInformation = $userInformation;
        $resourceOwnerName = $userInformation->getResourceOwner()->getName();

        if ($event->getUser()->hasRole('ROLE_MODEL')) {
            if ($resourceOwnerName == 'facebook') {
                $this->fbParams = ['access_token' => $userInformation->getAccessToken()];
                $this->fetchFacebookPhotosFromAlbums();
            }
        }
    }

    /**
     * Fetches several photos from facebook for user.
     */
    protected function fetchFacebookPhotosFromAlbums()
    {
        try {
            $this->fetchFacebookMaxCountAlbum();

            if (($album = $this->fetchFacebookMaxCountAlbum()) && is_array($album)) {
                $this->fetchFacebookAlbumPhotos($album);
            }

        } catch (RuntimeException $e) {
            return;
        }
    }

    /**
     * Fetches the data for the album with the max photos.
     *
     * @return null|array
     */
    protected function fetchFacebookMaxCountAlbum()
    {
        $client = $this->container->get('app.facebook.client');
        $albumsResult = $client->get('/me/albums', null, [
            'query' => $this->fbParams
        ])->send();
        $data = $albumsResult->json();

        // Find album with max photos
        $maxCountAlbum = null;
        foreach ($data['data'] as $album) {
            if (!$maxCountAlbum || $maxCountAlbum['count'] < $album['count']) {
                $maxCountAlbum = $album;
            }
        }

        return $maxCountAlbum;
    }

    /**
     * @param array $album
     */
    protected function fetchFacebookAlbumPhotos(array $album)
    {
        $client = $this->container->get('app.facebook.client');
        $albumsResult = $client->get('/'.$album['id'].'/photos', null, [
            'query' => $this->fbParams
        ])->send();
        $data = $albumsResult->json();
        $photos = array_slice($data['data'], 0, 5);

        foreach ($photos as $photo) {
            $this->fetchFacebookPhoto($photo);
        }
    }

    /**
     * @param $photo
     * @return null
     */
    protected function fetchFacebookPhoto($photo)
    {
        $userPhoto = $this->container->get('app.user_manager')->downloadProfilePicture($this->user, $photo['source']);
        if ($userPhoto) {
            $userPhoto->setTitle($photo['name']);
            $this->container->get('doctrine')->getManager()->flush();
        }

        return $userPhoto;
    }
}