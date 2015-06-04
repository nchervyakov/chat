<?php

namespace AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;

class EditConversationType extends AbstractType
{
    /**
     * @var AuthorizationChecker
     */
    protected $authChecker;

    function __construct(AuthorizationChecker $checker)
    {
        $this->authChecker = $checker;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
       $builder->add('clientAgreeToPay', null, [
           'disabled' => !$this->authChecker->isGranted('ROLE_CLIENT')
       ]);
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Conversation',
            'method' => 'PUT'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'edit_conversation';
    }
}
