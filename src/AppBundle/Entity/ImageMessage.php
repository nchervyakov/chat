<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMSSerializer;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * TextMessage
 * @ORM\Entity()
 * @Vich\Uploadable()
 *
 * @JMSSerializer\ExclusionPolicy("ALL")
 */
class ImageMessage extends ParticipantMessage
{
    /**
     * @var UploadedFile
     * @Vich\UploadableField(mapping="image_message", fileNameProperty="image")
     * @Assert\NotBlank(message="Image file is required", groups={"Default", "create"})
     */
    private $imageFile;

    /**
     * @var string
     * @ORM\Column(name="image", type="string", length=255)
     *
     * @JMSSerializer\Expose()
     * @JMSSerializer\Groups({"user_read", "message_list"})
     * @JMSSerializer\Type("string")
     * @JMSSerializer\Accessor(getter="getCheckedForDeletionImage")
     */
    private $image;

    /**
     * @return UploadedFile
     */
    public function getImageFile()
    {
        return $this->imageFile;
    }

    /**
     * @param UploadedFile $imageFile
     * @return $this
     */
    public function setImageFile($imageFile)
    {
        $this->imageFile = $imageFile;

        return $this;
    }

    /**
     * @return string
     */
    public function getImage()
    {
        return $this->image;
    }

    public function getCheckedForDeletionImage()
    {
        return $this->isDeletedByUser() ? null : $this->image;
    }

    /**
     * @param string $image
     * @return $this
     */
    public function setImage($image)
    {
        $this->image = $image;

        return $this;
    }

    public function __toString()
    {
        return $this->image;
    }
}
