<?php
/**
 * Created with IntelliJ IDEA by Nick Chervyakov.
 * User: Nikolay Chervyakov 
 * Date: 21.05.2015
 * Time: 14:36
  */



namespace AppBundle\Model;


use AppBundle\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation as JMSSerializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class UserCollection
 * @package AppBundle\Model
 * @JMSSerializer\XmlRoot("users")
 */
class UserCollection extends AbstractRestCollection
{
    /**
     * @var ArrayCollection|ArrayCollection<AppBundle\Entity\User>
     * @JMSSerializer\Type("array<AppBundle\Entity\User>")
     * @JMSSerializer\XmlList("user", inline=true)
     * @JMSSerializer\Groups({"user_read"})
     */
    private $users;

    /**
     * UserCollection constructor.
     * @param User[]|array $users
     * @param int $page
     * @param int $perPage
     */
    public function __construct($users = [], $page = 1, $perPage = 10)
    {
        if (!($users instanceof ArrayCollection)) {
            if (is_array($users)) {
                $users = new ArrayCollection($users);
            } else {
                $users = new ArrayCollection();
            }
        }
        $this->users = $users;
        $this->setPage($page);
        $this->setPerPage($perPage);
    }

    /**
     * @return ArrayCollection<AppBundle\Entity\User>
     */
    public function getUsers()
    {
        return $this->users;
    }

    /**
     * @param ArrayCollection|ArrayCollection<AppBundle\Entity\User>|array $users
     */
    public function setUsers($users)
    {
        $this->users = $users;
    }
}