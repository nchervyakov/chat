<?php
/**
 * Created with IntelliJ IDEA by Nick Chervyakov.
 * User: Nikolay Chervyakov 
 * Date: 21.05.2015
 * Time: 14:36
  */



namespace AppBundle\Model;


use AppBundle\Entity\Group;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation as JMSSerializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class GroupCollection
 * @package AppBundle\Model
 * @JMSSerializer\XmlRoot("groups")
 */
class GroupCollection extends AbstractRestCollection
{
    /**
     * @var ArrayCollection|ArrayCollection<AppBundle\Entity\Group>
     * @JMSSerializer\Type("array<AppBundle\Entity\Group>")
     * @JMSSerializer\XmlList("group", inline=true)
     */
    private $groups;

    /**
     * GroupCollection constructor.
     * @param Group[]|array $groups
     * @param int $page
     * @param int $perPage
     */
    public function __construct($groups = [], $page = 1, $perPage = 10)
    {
        if (!($groups instanceof ArrayCollection)) {
            if (is_array($groups)) {
                $groups = new ArrayCollection($groups);
            } else {
                $groups = new ArrayCollection();
            }
        }
        $this->groups = $groups;
        $this->setPage($page);
        $this->setPerPage($perPage);
    }

    /**
     * @return ArrayCollection
     */
    public function getGroups()
    {
        return $this->groups;
    }

    /**
     * @param ArrayCollection $groups
     */
    public function setGroups($groups)
    {
        $this->groups = $groups;
    }
}