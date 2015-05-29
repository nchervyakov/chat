<?php
/**
 * Created by IntelliJ IDEA.
 * User: Nikolay Chervyakov 
 * Date: 13.03.2015
 * Time: 15:44
 */


namespace AppBundle\Form\Type;


use AppBundle\Entity\UserPhoto;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Class UserPhotoType
 * @package AppBundle\Form\Type
 */
class UserPhotoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($options['title']) {
            $builder->add('title', null, [
                'required' => false,
                'error_bubbling' => true,
            ]);
        }

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $data = $event->getData();
            $form = $event->getForm();

            if (!$data) {
                $event->setData(new UserPhoto());
            }

            $form->add('file', 'vich_image', [
                'required' => true,
                'label' => false,
                'error_bubbling' => true
            ]);
        });
    }

    /**
     * @param OptionsResolverInterface|OptionsResolver $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'AppBundle\Entity\UserPhoto',
            'title' => true
        ]);
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'user_photo';
    }
}