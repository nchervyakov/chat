<?php
/**
 * Created by IntelliJ IDEA.
 * User: Nikolay Chervyakov 
 * Date: 06.03.2015
 * Time: 16:28
 */

namespace AppBundle\DataFixtures\ORM;


use AppBundle\Entity\User;
use AppBundle\Entity\UserPhoto;
use Faker\Provider\Image;
use Hautelook\AliceBundle\Alice\DataFixtureLoader;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Class FixtureLoader
 * @package AppBundle\DataFixtures\ORM
 */
class FixtureLoader extends DataFixtureLoader
{
    protected function getProcessors()
    {
        return parent::getProcessors();
    }

    protected function getFixtures() {
        $fixtures = [
            __DIR__.'/../../Resources/fixtures/emoticons.yml',
            __DIR__.'/../../Resources/fixtures/fixtures.yml'
        ];

        /** @var \AppKernel $kernel */
        $kernel = $this->container->get('kernel');

        if (in_array($kernel->getEnvironment(), array('dev', 'test'))) {
            $fixtures[] = __DIR__.'/../../Resources/fixtures/dev_fixtures.yml';
        }

        return $fixtures;
    }

    public function convertDateTime($date)
    {
        if (empty($date)) {
            return null;
        }

        return new \DateTime($date);
    }

    public function dt($date)
    {
        return $this->convertDateTime($date);
    }

    public function salt()
    {
        //mt_srand($this->container->getParameter('hautelook_alice.seed'));
        return base_convert(sha1(mt_rand()), 16, 36);
    }

    public function firstNameByGender($gender)
    {
        $loader = $this->container->get('hautelook_alice.loader.yaml');
        $name = $loader->fake('firstName','en', $this->expandGender($gender));
        return $name;
    }

    public function userRolesByGender($gender)
    {
        $roles = [
            User::GENDER_MALE => User::ROLE_CLIENT,
            User::GENDER_FEMALE => User::ROLE_MODEL
        ];
        return [$roles[$gender]];
    }

    public function userGroupByGender($gender)
    {
        $loader = $this->container->get('hautelook_alice.loader.yaml');
        if ($gender == User::GENDER_MALE) {
            return $loader->getReference('Clients');

        } else if ($gender == User::GENDER_FEMALE) {
            return $loader->getReference('Models');
        }

        return null;
    }

    public function expandGender($gender)
    {
        if ($gender == User::GENDER_MALE) {
            return 'male';
        } else if ($gender == User::GENDER_FEMALE) {
            return 'female';
        }
        return $gender;
    }

    public function rootDir($path = '')
    {
        return implode('/', [realpath(__DIR__.'/../../../../'), $path]);
    }

    public function imageEx($dir = 'web/uploads', $width = 640, $height = 480, $category = null, $fullPath = true)
    {
        // Validate directory path
        if (!is_dir($dir) || !is_writable($dir)) {
            throw new \InvalidArgumentException(sprintf('Cannot write to directory "%s"', $dir));
        }

        // Generate a random filename. Use the server address so that a file
        // generated at the same time on a different server won't have a collision.
        $name = md5(mt_rand() . (empty($_SERVER['SERVER_ADDR']) ? '' : $_SERVER['SERVER_ADDR']));
        $filename = $name . '.jpg';
        $filepath = realpath(__DIR__ . '/../../../../') . '/' . $dir . DIRECTORY_SEPARATOR . $filename;

        if (file_exists($filepath) && is_file($filepath)) {
            return $fullPath ? $filepath : $filename;
        }

        $url = Image::imageUrl($width, $height, $category);

        // save file
        if (function_exists('curl_exec')) {
            // use cURL
            $fp = fopen($filepath, 'w');
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_FILE, $fp);
            $success = curl_exec($ch);
            curl_close($ch);
            fclose($fp);
        } elseif (ini_get('allow_url_fopen')) {
            // use remote fopen() via copy()
            $success = copy($url, $filepath);
        } else {
            return new \RuntimeException('The image formatter downloads an image from a remote HTTP server. Therefore, it requires that PHP can request remote hosts, either via cURL or fopen()');
        }

        if (!$success) {
            // could not contact the distant URL or HTTP error - fail silently.
            return false;
        }

        return $fullPath ? $filepath : $filename;
    }

    public function uploadPhoto($dir = 'web/uploads', $width = 640, $height = 480, $category = null, $fullPath = true)
    {
        $imagePath = $this->imageEx($dir, $width, $height, $category, $fullPath);

        if ($imagePath) {
            $targetImagePath = preg_replace('/^(.*)(\.jpe?g)$/i', '$1_tmp$2', $imagePath);
            copy($dir.'/'.$imagePath, $dir.'/'.$targetImagePath);
            $userPhoto = new UserPhoto();
            //var_dump($dir . '/' . $imagePath);exit;
            $uploadedFile = new UploadedFile($dir . '/' . $targetImagePath, $targetImagePath, null, null, null, true);
            $userPhoto->setFile($uploadedFile);
            $this->container->get('vich_uploader.upload_handler')->upload($userPhoto, 'file');
            //$this->container->get('app.user_manager')->pregeneratePhotoThumbs($userPhoto);
            return $userPhoto->getFileName();
        }

        return null;
    }
}