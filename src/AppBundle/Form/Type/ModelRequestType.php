<?php

namespace AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ModelRequestType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstName', null, [
                'label' => 'model_request.first_name'
            ])
            ->add('lastName', null, [
                'label' => 'model_request.last_name'
            ])
            ->add('email')
            ->add('facebookURL', null, [
                'label' => 'model_request.facebook_url',
                'required' => false,
                'empty_data' => ''
            ])
            ->add('instagramURL', null, [
                'label' => 'model_request.instagram_url',
                'required' => false,
                'empty_data' => ''
            ])
            ->add('message')
        ;
    }
    
    /**
     * @param OptionsResolverInterface|OptionsResolver $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\ModelRequest'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'model_request';
    }
}
