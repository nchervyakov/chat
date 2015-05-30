<?php
/**
 * Created with IntelliJ IDEA by Nick Chervyakov.
 * User: Nikolay Chervyakov 
 * Date: 21.05.2015
 * Time: 14:36
  */



namespace AppBundle\Model;


use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation as JMSSerializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class ModelStatCollection
 * @package AppBundle\Model
 * @JMSSerializer\XmlRoot("model_stats")
 */
class ModelStatCollection extends AbstractRestCollection
{
    /**
     * @var ArrayCollection
     * @JMSSerializer\Type("array<AppBundle\Entity\Conversation>")
     * @JMSSerializer\XmlList("conversation")
     * @JMSSerializer\Groups({"model_read", "admin_read", "model_stat"})
     */
    private $conversations;

    /**
     * @var double
     *
     * @JMSSerializer\Type("double")
     * @JMSSerializer\Groups({"model_read", "admin_read", "model_stat"})
     */
    private $totalEarnings;

    /**
     * @var int
     * @JMSSerializer\Type("integer")
     * @JMSSerializer\Groups({"model_read", "admin_read", "model_stat"})
     */
    private $totalSeconds;

    /**
     * ModelStatCollection constructor.
     * @param array $conversations
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
        $this->conversations = $conversations;
        $this->setPage($page);
        $this->setPerPage($perPage);
    }

    /**
     * @return ArrayCollection|array
     */
    public function getConversations()
    {
        return $this->conversations;
    }

    /**
     * @param ArrayCollection|array $conversations
     */
    public function setConversations($conversations)
    {
        $this->conversations = $conversations;
    }

    /**
     * @return float
     */
    public function getTotalEarnings()
    {
        return $this->totalEarnings;
    }

    /**
     * @param float $totalEarnings
     */
    public function setTotalEarnings($totalEarnings)
    {
        $this->totalEarnings = $totalEarnings;
    }

    /**
     * @return int
     */
    public function getTotalSeconds()
    {
        return $this->totalSeconds;
    }

    /**
     * @param int $totalSeconds
     */
    public function setTotalSeconds($totalSeconds)
    {
        $this->totalSeconds = $totalSeconds;
    }
}