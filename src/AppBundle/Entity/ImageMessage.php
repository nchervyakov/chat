<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serialize;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * TextMessage
 * @ORM\Entity()
 * @Vich\Uploadable()
 */
class ImageMessage extends Message
{
    /**
     * @var UploadedFile
     * @Vich\UploadableField(mapping="image_message", fileNameProperty="image")
     */
    private $imageFile;

    /**
     * @var string
     * @ORM\Column(name="image", type="string", length=255)
     * @Assert\NotBlank(message="Image file is required")
     * @Serialize\Expose()
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

    /**
     * @param string $image
     * @return $this
     */
    public function setImage($image)
    {
        $this->image = $image;

        return $this;
    }

    public function getDiscriminator()
    {
        return 'image';
    }
}
