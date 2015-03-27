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

use AppBundle\Entity\User;
use FOS\UserBundle\Model\UserManager;
use Symfony\Component\Form\FormBuilderInterface;
use FOS\UserBundle\Form\Type\ProfileFormType as BaseType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

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
        parent::buildForm($builder, $options);

        $builder
            ->add('firstname')
            ->add('lastname')
            ->remove('username')
            ->remove('current_password')
            ->add('thumbnail', 'user_photo', [
                'required' => false,
                'title' => false
            ]);

        $um = $this->userManager;
        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) use ($um) {
            $user = $event->getData();
            /** @var User $user */
            if ($user) {
                $user->setUsername($user->getEmail());
                $um->updateCanonicalFields($user);
            }
        });
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
