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
 * Class EmoticonCollection
 * @package AppBundle\Model
 * @JMSSerializer\XmlRoot("emoticons")
 */
class EmoticonCollection extends AbstractRestCollection
{
    /**
     * @var ArrayCollection|ArrayCollection<AppBundle\Entity\Emoticon>
     * @JMSSerializer\Type("array<AppBundle\Entity\Emoticon>")
     * @JMSSerializer\XmlList("emoticon", inline=true)
     */
    private $emoticons;

    /**
     * EmoticonCollection constructor.
     * @param array $emoticons
     * @param int $page
     * @param int $perPage
     */
    public function __construct($emoticons = [], $page = 1, $perPage = 10)
    {
        if (!($emoticons instanceof ArrayCollection)) {
            if (is_array($emoticons)) {
                $emoticons = new ArrayCollection($emoticons);
            } else {
                $emoticons = new ArrayCollection();
            }
        }
        $this->emoticons = $emoticons;
        $this->setPage($page);
        $this->setPerPage($perPage);
    }

    /**
     * @return ArrayCollection
     */
    public function getEmoticons()
    {
        return $this->emoticons;
    }

    /**
     * @param ArrayCollection $emoticons
     */
    public function setEmoticons($emoticons)
    {
        $this->emoticons = $emoticons;
    }
}