<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMSSerializer;

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
     *
     * @JMSSerializer\Expose()
     * @JMSSerializer\Type("string")
     * @JMSSerializer\Groups({"user_read", "message_list"})
     */
    private $notificationType = self::TYPE_INFO;

    /**
     * @return string
     */
    public function getNotificationType()
    {
        return $this->notificationType;
    }

    /**
     * @param string $notificationType
     */
    public function setNotificationType($notificationType)
    {
        $this->notificationType = $notificationType;
    }

    /**
     * @return string
     * @JMSSerializer\VirtualProperty()
     * @JMSSerializer\Groups({"user_read", "message_list"})
     */
    public function getType()
    {
        return 'notification';
    }
}
