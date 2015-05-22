<?php
/**
 * Created with IntelliJ IDEA by Nick Chervyakov.
 * User: Nikolay Chervyakov 
 * Date: 21.05.2015
 * Time: 16:44
  */



namespace AppBundle\Model;


use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as JMSSerializer;

/**
 * Base collection class for rest results.
 * @package AppBundle\Model
 */
class AbstractRestCollection
{
    /**
     * @var int
     * @Assert\NotBlank()
     * @JMSSerializer\XmlAttribute()
     * @JMSSerializer\Groups({"user_read"})
     */
    private $page = 1;

    /**
     * @var int Limit
     * @Assert\NotBlank()
     * @JMSSerializer\XmlAttribute()
     * @JMSSerializer\Groups({"user_read"})
     */
    private $perPage = 10;

    /**
     * @var int
     * @JMSSerializer\XmlAttribute()
     * @JMSSerializer\Groups({"user_read"})
     */
    private $pageCount = 0;

    /**
     * @var int
     * @JMSSerializer\XmlAttribute()
     * @JMSSerializer\Groups({"user_read"})
     */
    private $totalItemsCount = 0;

    /**
     * @return int
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * @param int $page
     */
    public function setPage($page)
    {
        $this->page = (int) $page;
    }

    /**
     * @return int
     */
    public function getPerPage()
    {
        return $this->perPage;
    }

    /**
     * @param int $perPage
     */
    public function setPerPage($perPage)
    {
        $this->perPage = (int) $perPage;
    }

    /**
     * @return int
     */
    public function getPageCount()
    {
        return $this->pageCount;
    }

    /**
     * @param int $pageCount
     */
    public function setPageCount($pageCount)
    {
        $this->pageCount = $pageCount;
    }

    /**
     * @return int
     */
    public function getTotalItemsCount()
    {
        return $this->totalItemsCount;
    }

    /**
     * @param int $totalItemsCount
     */
    public function setTotalItemsCount($totalItemsCount)
    {
        $this->totalItemsCount = $totalItemsCount;
    }
}