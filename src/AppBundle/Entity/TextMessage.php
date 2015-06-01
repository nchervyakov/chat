<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TextMessage
 * @ORM\Entity()
 */
class TextMessage extends ParticipantMessage
{
    public function getDiscriminator()
    {
        return 'text';
    }
}
