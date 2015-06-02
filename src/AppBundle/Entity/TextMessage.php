<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * TextMessage
 * @ORM\Entity()
 */
class TextMessage extends ParticipantMessage
{
    /**
     * @var string
     * @Assert\NotBlank(groups={"Default", "create"})
     */
    protected $content;
}
