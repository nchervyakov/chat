<?php

/*
 * This file is part of the FOSUserBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AppBundle\Form\Type;

use FOS\UserBundle\Model\UserManager;
use Symfony\Component\Form\FormBuilderInterface;
use FOS\UserBundle\Form\Type\ProfileFormType as BaseType;

/**
 * Class ProfileFormType
 * @package AppBundle\Form\Type
 */
class ProfileFormType extends BaseType
{
    /** @var \FOS\UserBundle\Model\UserManager */
    protected $userManager;

    public function __construct($class, UserManager $userManager)
    {
        parent::__construct($class);
        $this->userManager = $userManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstName')
            ->add('lastName')
            ->remove('plainPassword');

        parent::buildForm($builder, $options);

//        $um = $this->userManager;
//        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) use ($um) {
//            $user = $event->getData();
//            /** @var User $user */
//            if ($user) {
//                $user->setUsername($user->getEmail());
//                $um->updateCanonicalFields($user);
//            }
//        });
    }

    public function getName()
    {
        return 'user_profile';
    }

    public function getParent()
    {
        return 'fos_user_profile';
    }
}
