<?php

namespace PaymentBundle\Admin;

//use AppBundle\Tools\DateTimeServices;
use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Show\ShowMapper;

/**
 * Class PaymentAdmin
 * @package AppBundle\Admin
 */
class PaymentAdmin extends Admin
{
    /**
     * @param DatagridMapper $datagridMapper
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('id')
            ->add('clientId')
            ->add('clientEmail')
            ->add('amount')
            ->add('description')
            ->add('dateAdded')
        ;
    }

    /**
     * @param ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('id', null, [
                'route' => ['name' => 'show']
            ])
//            ->add('aliases', null, [
//                'template' => ':CRUD:list_array_with_delimeters.html.twig'
//            ])

            ->add('order', null, [
                'associated_property' => 'id',
                'route' => ['name' => 'show']
            ])
            ->add('clientId')
            ->add('clientEmail')
            ->add('totalAmount', null, [
                'template' => ':CRUD:payment_amount_list_field.html.twig',
                'label' => 'Amount (USD)',
                //'row_align' => 'right',
            ])
            ->add('description')
            ->add('dateAdded')
            ->add('_action', 'actions', array(
                'actions' => array(
                    'show' => array(),
                    //'edit' => array(),
                    //'delete' => array(),
                )
            ))
        ;
    }

//    /**
//     * @param FormMapper $formMapper
//     */
//    protected function configureFormFields(FormMapper $formMapper)
//    {
//        parent::configureFormFields($formMapper);
//        $formMapper
//            ->with('Emoticon', ['class' => 'col-lg-6 col-md-12'])
//                ->add('symbol')
//                ->add('aliases', 'sonata_type_native_collection', [
//                    'allow_add' => true,
//                    'allow_delete' => true,
//                    'required' => false,
//                    'options' => [
//                        'required' => false
//                    ]
//                ])
//                ->add('iconFile', 'vich_image', [
//                    'required' => false,
//                    'label' => false,
//                    'error_bubbling' => true,
//                    'download_link' => false,
//                    'allow_delete' => false
//                ])
//                ->add('sortOrder')
//                ->add('dateAdded', 'sonata_type_datetime_picker', [
//                    'format' => DateTimeServices::FORMAT_DATETIME_DOTTED
//                ])
//            ->end()
//        ;
//    }

    /**
     * @param ShowMapper $showMapper
     */
    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->add('id')
            ->add('clientId')
            ->add('clientEmail')
            ->add('totalAmount')
            ->add('description')
            ->add('dateAdded')
            ->add('dateUpdated')
            ->add('detailsFormatted', null, [
                'label' => 'Details'
            ])
        ;
    }

    public function getTemplate($name)
    {
        if ($name === 'show') {
            return ':CRUD:show_abstract_order.html.twig';

        } else if ($name === 'list') {
            return ':CRUD:list_abstract_order.html.twig';
        }

        return parent::getTemplate($name);
    }
}
