<?php
/**
 * Created with IntelliJ IDEA by Nick Chervyakov.
 * User: Nikolay Chervyakov 
 * Date: 21.05.2015
 * Time: 14:36
  */



namespace AppBundle\Model;


use AppBundle\Entity\Message;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation as JMSSerializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class MessageCollection
 * @package AppBundle\Model
 *
 * @JMSSerializer\XmlRoot("messages")
 */
class MessageCollection extends AbstractRestCollection
{
    /**
     * @var ArrayCollection|ArrayCollection<AppBundle\Entity\Message>
     *
     * @JMSSerializer\Type("array")
     * @JMSSerializer\XmlList("message", inline=true)
     * @JMSSerializer\Groups({"user_read", "message_list"})
     */
    private $messages;

    /**
     * MessageCollection constructor.
     * @param Message[]|array $messages
     * @param int $page
     * @param int $perPage
     */
    public function __construct($messages = [], $page = 1, $perPage = 10)
    {
        if (!($messages instanceof ArrayCollection)) {
            if (is_array($messages)) {
                $messages = new ArrayCollection($messages);
            } else {
                $messages = new ArrayCollection();
            }
        }
        $this->messages = $messages;
        $this->setPage($page);
        $this->setPerPage($perPage);
    }

    /**
     * @return ArrayCollection<AppBundle\Entity\Message>
     */
    public function getMessages()
    {
        return $this->messages;
    }

    /**
     * @param ArrayCollection|ArrayCollection<AppBundle\Entity\Message>|array $messages
     */
    public function setMessages($messages)
    {
        $this->messages = $messages;
    }
}