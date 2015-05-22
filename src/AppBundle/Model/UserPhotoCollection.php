<?php
/**
 * Created with IntelliJ IDEA by Nick Chervyakov.
 * User: Nikolay Chervyakov 
 * Date: 21.05.2015
 * Time: 14:36
  */



namespace AppBundle\Model;


use AppBundle\Entity\UserPhoto;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation as JMSSerializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class UserPhotoCollection
 * @package AppBundle\Model
 * @JMSSerializer\XmlRoot("user_photos")
 */
class UserPhotoCollection extends AbstractRestCollection
{
    /**
     * @var ArrayCollection|ArrayCollection<AppBundle\Entity\UserPhoto>
     *
     * @JMSSerializer\Type("array<AppBundle\Entity\UserPhoto>")
     * @JMSSerializer\XmlList("user_photo", inline=true)
     * @JMSSerializer\Groups({"user_read"})
     */
    private $userPhotos;

    /**
     * UserPhotoCollection constructor.
     * @param UserPhoto[]|array $userPhotos
     * @param int $page
     * @param int $perPage
     */
    public function __construct($userPhotos = [], $page = 1, $perPage = 10)
    {
        if (!($userPhotos instanceof ArrayCollection)) {
            if (is_array($userPhotos)) {
                $userPhotos = new ArrayCollection($userPhotos);
            } else {
                $userPhotos = new ArrayCollection();
            }
        }
        $this->userPhotos = $userPhotos;
        $this->setPage($page);
        $this->setPerPage($perPage);
    }

    /**
     * @return ArrayCollection<AppBundle\Entity\UserPhoto>
     */
    public function getUserPhotos()
    {
        return $this->userPhotos;
    }

    /**
     * @param ArrayCollection|ArrayCollection<AppBundle\Entity\UserPhoto>|array $userPhotos
     */
    public function setUserPhotos($userPhotos)
    {
        $this->userPhotos = $userPhotos;
    }
}