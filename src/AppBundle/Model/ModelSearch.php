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
    /**
     * @var integer
     * @Range(min="0")
     */
    private $from;

    /**
     * @var integer
     * @Range(max="150")
     */
    private $to;

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
        $this->from = $from;
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
        $this->to = $to;
    }

    public function checkAgeInterval(ExecutionContextInterface $context)
    {
        if ($this->to < $this->from) {
            $context->buildViolation('model_search.validation.incorrect_age_interval')->addViolation();
        }
    }
}