<?php
/**
 * Created by IntelliJ IDEA.
 * User: Nikolay Chervyakov
 * Date: 10.03.2015
 * Time: 11:08
 */


namespace AppBundle\Form\Type;

use AppBundle\Entity\ImageMessage;
use AppBundle\Entity\Message;
use AppBundle\Entity\TextMessage;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;
use Symfony\Component\Validator\Constraints as Assert;

class MessageType extends AbstractType
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
        $messageTypes = ['text', 'image'];

        if (!$options['patch']) {
            $builder->add('discriminator', 'choice', [
                'mapped' => false,
                'choices' => array_combine($messageTypes, $messageTypes),
                'constraints' => [
                    new Assert\NotBlank(['groups' => ['create']])
                ],
                'error_bubbling' => false
            ]);
        }

        $checker = $this->authChecker;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($options, $checker) {
            /** @var Message $message */
            $message = $event->getData();

            if (!$message) {
                return;
            }

            $form = $event->getForm();
            $form->add('deletedByUser', null, [
                'disabled' => $message->isDeletedByUser()
            ]);

            $form->add('seenByClient', null, [
                'disabled' => !$checker->isGranted('ROLE_CLIENT')
            ]);

            $form->add('seenByModel', null, [
                'disabled' => !$checker->isGranted('ROLE_MODEL'),
            ]);
        });

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) use ($options) {
            $data = $event->getData();
            $form = $event->getForm();
            $type = $data['discriminator'];

            if ($options['patch']) {
                return;
            }

            if (!$form->getData()) {
                if ($type == 'image') {
                    $message = new ImageMessage();
                    $form->add('imageFile', 'vich_image', [
                        'required' => true,
                        'label' => false,
                        'error_bubbling' => false
                    ]);

                } else {
                    $message = new TextMessage();
                    $message->setContent($data['content']);
                    $form->add('content', null, ['error_bubbling' => false]);
                }

                $form->setData($message);
            }
        });
    }
    
    /**
     * @param OptionsResolverInterface|OptionsResolver $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Message',
            'required' => true,
            'api' => false,
            'patch' => false
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'message';
    }
}
