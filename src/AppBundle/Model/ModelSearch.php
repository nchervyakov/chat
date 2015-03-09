<?php
/**
 * Created by IntelliJ IDEA.
 * User: Nikolay Chervyakov 
 * Date: 06.03.2015
 * Time: 12:49
 */


namespace AppBundle\Model;


use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * Class ModelSearch
 * @package AppBundle\Model
 * @Callback(methods={"checkAgeInterval"})
 */
class ModelSearch
{
    const AGE_MIN = 18;
    const AGE_MAX = 100;

    /**
     * @var integer
     * @Range(min="0", max="130")
     */
    private $from = self::AGE_MIN;

    /**
     * @var integer
     * @Range(min="0", max="130")
     */
    private $to = self::AGE_MAX;

    /**
     * @var bool
     */
    private $withPhoto = false;

    /**
     * @return int
     */
    public function getFrom()
    {
        return $this->from;
    }

    /**
     * @param int $from
     */
    public function setFrom($from)
    {
        $this->from = is_numeric($from) ? (int) $from : null;
    }

    /**
     * @return int
     */
    public function getTo()
    {
        return $this->to;
    }

    /**
     * @param int $to
     */
    public function setTo($to)
    {
        $this->to = is_numeric($to) ? (int) $to : null;;
    }

    /**
     * Checks that "from" field is less or equal to "to" field
     * @param ExecutionContextInterface $context
     */
    public function checkAgeInterval(ExecutionContextInterface $context)
    {
        if (is_numeric($this->from) && is_numeric($this->to) && $this->to < $this->from) {
            $context->buildViolation('model_search.validation.incorrect_age_interval')
                ->setTranslationDomain('messages')
                ->addViolation();
        }
    }

    /**
     * @return array Possible ages list.
     */
    public static function getAgeRange()
    {
        return range(self::AGE_MIN, self::AGE_MAX);
    }

    /**
     * @return array A list of values for the age fields in search form.
     */
    public static function getAgeRangeList()
    {
        $ageRange = self::getAgeRange();
        return array_combine(array_values($ageRange), $ageRange);
    }

    /**
     * @return boolean
     */
    public function isWithPhoto()
    {
        return $this->withPhoto;
    }

    /**
     * @param boolean $withPhoto
     */
    public function setWithPhoto($withPhoto)
    {
        $this->withPhoto = (boolean) $withPhoto;
    }
}