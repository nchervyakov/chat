<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * NotificationMessage
 * @ORM\Entity()
 */
class NotificationMessage extends Message
{
    const TYPE_INFO = 'info';
    const TYPE_WARNING = 'warning';
    const TYPE_ERROR = 'error';

    /**
     * @var string
     * @ORM\Column(name="notification_type", type="string", length=16, options={"default": "info"})
     */
    private $type = self::TYPE_INFO;

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }
}
