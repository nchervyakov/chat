<?php
/**
 * Created by IntelliJ IDEA.
 * User: Nikolay Chervyakov
 * Date: 05.05.2014
 * Time: 17:19
 */


namespace AppBundle\Form\Type;

use AppBundle\Entity\User;
use FOS\UserBundle\Form\Type\RegistrationFormType as BaseType;
use FOS\UserBundle\Model\UserManager;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

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

        if ($options['choose_gender']) {
            $builder
                ->add('gender', 'choice', [
                    'choices' => User::getGendersLabels(),
                    'multiple' => false,
                    'expanded' => true,
                    'constraints' => [
                        new NotBlank(['groups' => ['AppRegistration', 'AppProfile']])
                    ],
                ]);
        }

        if ($options['api']) {
            $builder
                ->add('type', 'choice', [
                    'choices' => [
                        'model' => 'model',
                        'client' => 'client'
                    ],
                    'multiple' => false,
                    'expanded' => true,
                    'mapped' => false,
                    'constraints' => [
                        new NotBlank(['message' => 'User type is required', 'groups' => 'AppRegistration'])
                    ],
                ]);
        }

        $builder
            ->add('firstname')
            ->add('lastname')
            ->remove('plainPassword')
            ->remove('username')
        ;

        if ($options['api']) {
            $builder->add('dateOfBirth', 'birthday', [
                'widget' => 'single_text',
                'format' => 'y-M-d'
            ]);

        } else {
            $builder->add('dateOfBirth', 'birthday');
        }

        $um = $this->userManager;
        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) use ($um) {
            $user = $event->getData();
            /** @var \AppBundle\Entity\User $user */
            if ($user) {
                if (!$user->getUsername() || !$user->getId()) {
                    $user->setUsername($user->getEmail());
                }
                if (!$user->getPassword()) { // Set password as empty string for users without password (everyone except admin)
                    $user->setPassword('');
                }
                $um->updateCanonicalFields($user);
            }
        });
    }

    /**
     * @param OptionsResolverInterface|OptionsResolver $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        parent::setDefaultOptions($resolver);
        $resolver->setDefaults([
            'csrf_field_name' => '_user_registration_token',
            'choose_gender' => true,
            'api' => false
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