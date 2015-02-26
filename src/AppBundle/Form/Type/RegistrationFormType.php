<?php
/**
 * Created by IntelliJ IDEA.
 * User: Nikolay Chervyakov
 * Date: 05.05.2014
 * Time: 17:19
 */


namespace AppBundle\Form\Type;

use FOS\UserBundle\Form\Type\RegistrationFormType as BaseType;
use FOS\UserBundle\Model\UserManager;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Class RegistrationFormType
 * @package AppBundle\Form\Type
 */
class RegistrationFormType extends BaseType
{
    protected $userManager;

    public function __construct($class, UserManager $userManager)
    {
        parent::__construct($class);
        $this->userManager = $userManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('firstName')
            ->add('lastName')
            ->remove('plainPassword');

//        $um = $this->userManager;
//        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) use ($um) {
//            $user = $event->getData();
//            /** @var \AppBundle\Entity\User $user */
//            if ($user) {
//                $user->setUsername($user->getEmail());
//                $um->updateCanonicalFields($user);
//            }
//        });
    }

    /**
     * @param OptionsResolverInterface|OptionsResolver $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        parent::setDefaultOptions($resolver);
        $resolver->setDefaults([
            'csrf_field_name' => '_user_registration_token',
        ]);
    }

    public function getName()
    {
        return 'user_registration';
    }

    public function getParent()
    {
        return 'fos_user_registration';
    }
} 