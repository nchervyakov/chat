<?php
/**
 * Created by IntelliJ IDEA.
 * User: Nikolay Chervyakov 
 * Date: 06.03.2015
 * Time: 12:42
 */


namespace AppBundle\Form\Type;


use AppBundle\Model\ModelSearch;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ModelSearchFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $ageRange = ModelSearch::getAgeRangeList();
        $builder
            ->add('from', 'choice', [
                'choices' => $ageRange,
                'error_bubbling' => true,
                'invalid_message' => 'Please select the valid "from" value.'
            ])
            ->add('to', 'choice', [
                'choices' => $ageRange,
                'error_bubbling' => true,
                'invalid_message' => 'Please select the valid "to" value.'
            ]);
    }

    /**
     * @param OptionsResolverInterface|OptionsResolver $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        parent::setDefaultOptions($resolver);
        $resolver->setDefaults([
            'data_class' => 'AppBundle\Model\ModelSearch',
            'csrf_protection' => false,
            'allow_extra_fields' => true,
            'name' => 's'
        ]);
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'model_search';
    }
}