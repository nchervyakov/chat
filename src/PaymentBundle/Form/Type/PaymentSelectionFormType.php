<?php
/**
 * Created with IntelliJ IDEA by Nick Chervyakov.
 * User: Nikolay Chervyakov
 * Date: 01.02.2016
 * Time: 15:48
 */


namespace PaymentBundle\Form\Type;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Range;

/**
 * Class PaymentSelectionFormType
 * @package PaymentBundle\Form\Type
 */
class PaymentSelectionFormType extends AbstractType
{
    /**
     * @var array
     */
    protected $variants;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $choices = [];
        foreach ($this->variants as $variant => $coins) {
            $choices["Pay $${variant} for $coins minutes chat with"] = $variant;
        }

        $choices['payment_selection.custom'] = 'custom';

        $builder->add('amount', 'choice', [
            'choices' => $choices,
            'choices_as_values' => true,
            'expanded' => true,
            'label' => 'payment_selection.amount'
        ]);

        $builder->add('custom', 'number', [
            'constraints' => [
                new Range([
                    'min' => 5,
                    'max' => 1000,
                    'minMessage' => 'payment_selection.min_error',
                    'maxMessage' => 'payment_selection.max_error',
                ])
            ],
            'error_bubbling' => true,
            'disabled' => true,
            'label' => 'payment_selection.custom'
        ]);

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $form = $event->getForm();
            $data = $event->getData();

            if ($data === null) {
                $data = [];
            }

            if ($data['amount'] === 'custom') {
                $custom = $form->get('custom')->getConfig();
                $options = $custom->getOptions();

                $newOptions = $options;
                $newOptions['disabled'] = false;
                $newOptions['constraints'][] = new NotBlank();

                $form->add('custom', $custom->getType()->getName(), $newOptions);
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
    }

    public function getName()
    {
        return 'payment_selection';
    }

    public function setVariants($variants)
    {
        if (!is_array($variants)) {
            $variants = [];
        }

        $this->variants = $variants;
    }
}