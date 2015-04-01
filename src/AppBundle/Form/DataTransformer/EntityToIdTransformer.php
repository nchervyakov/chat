<?php
/**
 * Created with IntelliJ IDEA by Nick Chervyakov.
 * User: Nikolay Chervyakov 
 * Date: 31.03.2015
 * Time: 15:15
  */

namespace AppBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Doctrine\Common\Persistence\ObjectManager;

class EntityToIdTransformer implements DataTransformerInterface
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;
    /**
     * @var string
     */
    protected $class;
    public function __construct(ObjectManager $objectManager, $class)
    {
        $this->objectManager = $objectManager;
        $this->class = $class;
    }
    public function transform($entity)
    {
        if (null === $entity) {
            return null;
        }
        return $entity->getId();
    }
    public function reverseTransform($id)
    {
        if (!$id) {
            return null;
        }
        $entity = $this->objectManager
            ->getRepository($this->class)
            ->find($id);
        if (null === $entity) {
            throw new TransformationFailedException();
        }
        return $entity;
    }
}