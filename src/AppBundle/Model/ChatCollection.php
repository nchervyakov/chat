<?php
/**
 * Created with IntelliJ IDEA by Nick Chervyakov.
 * User: Nikolay Chervyakov 
 * Date: 21.05.2015
 * Time: 14:36
  */



namespace AppBundle\Model;


use AppBundle\Entity\Conversation;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation as JMSSerializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class ChatCollection
 * @package AppBundle\Model
 * @JMSSerializer\XmlRoot("chats")
 */
class ChatCollection extends AbstractRestCollection
{
    /**
     * @var ArrayCollection|ArrayCollection<AppBundle\Entity\Conversation>
     * @JMSSerializer\Type("array<AppBundle\Entity\Conversation>")
     * @JMSSerializer\XmlList("chat", inline=true)
     * @JMSSerializer\Groups({"chat_list", "essential_public"})
     */
    private $chats;

    /**
     * ConversationCollection constructor.
     * @param Conversation[]|array $conversations
     * @param int $page
     * @param int $perPage
     */
    public function __construct($conversations = [], $page = 1, $perPage = 10)
    {
        if (!($conversations instanceof ArrayCollection)) {
            if (is_array($conversations)) {
                $conversations = new ArrayCollection($conversations);
            } else {
                $conversations = new ArrayCollection();
            }
        }
        $this->chats = $conversations;
        $this->setPage($page);
        $this->setPerPage($perPage);
    }

    /**
     * @return ArrayCollection<AppBundle\Entity\Conversation>
     */
    public function getChats()
    {
        return $this->chats;
    }

    /**
     * @param ArrayCollection|ArrayCollection<AppBundle\Entity\Conversation>|array $chats
     */
    public function setChats($chats)
    {
        $this->chats = $chats;
    }
}