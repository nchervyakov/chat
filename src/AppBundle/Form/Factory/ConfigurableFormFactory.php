<?php
/**
 * Created with IntelliJ IDEA by Nick Chervyakov.
 * User: Nikolay Chervyakov 
 * Date: 06.04.2015
 * Time: 16:27
  */



namespace AppBundle\Form\Factory;


use FOS\UserBundle\Form\Factory\FactoryInterface;
use Symfony\Component\Form\FormFactoryInterface;

class ConfigurableFormFactory implements FactoryInterface
{
    private $formFactory;
    private $name;
    private $type;
    private $validationGroups;
    private $options = [];

    public function __construct(FormFactoryInterface $formFactory, $name, $type, array $validationGroups = null, $options = [])
    {
        $this->formFactory = $formFactory;
        $this->name = $name;
        $this->type = $type;
        $this->validationGroups = $validationGroups;
        $this->options = $options;
    }

    public function createForm()
    {
        return $this->formFactory->createNamed($this->name, $this->type, null,
            array_merge($this->options, ['validation_groups' => $this->validationGroups]));
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param $option
     * @return mixed
     */
    public function getOption($option)
    {
        return $this->options[$option];
    }

    /**
     * @param $option
     * @param $value
     */
    public function setOption($option, $value)
    {
        $this->options[$option] = $value;
    }
}
