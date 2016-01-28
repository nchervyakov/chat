<?php
/**
 * Created with IntelliJ IDEA by Nick Chervyakov.
 * User: Nikolay Chervyakov
 * Date: 28.01.2016
 * Time: 16:34
 */


namespace AppBundle\Admin;

use Sonata\AdminBundle\Form\FormMapper;
use \Sonata\UserBundle\Admin\Entity\GroupAdmin as BaseGroupAdmin;
use Sonata\UserBundle\Form\Type\SecurityRolesType;


class GroupAdmin extends BaseGroupAdmin
{
    /**
     * {@inheritdoc}
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->tab('Group')
            ->with('General', array('class' => 'col-md-6'))
            ->add('name')
            ->end()
            ->end()
            ->tab('Security')
            ->with('Roles', array('class' => 'col-md-12'))
            ->add('roles', SecurityRolesType::class, array(
                'expanded' => true,
                'multiple' => true,
                'required' => false,
                'choices_as_values' => true
            ))
            ->end()
            ->end()
        ;
    }
}