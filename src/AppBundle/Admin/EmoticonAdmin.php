<?php

namespace AppBundle\Admin;

use AppBundle\Tools\DateTimeServices;
use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Component\Form\Extension\Core\Type\DateType;

class EmoticonAdmin extends Admin
{
    /**
     * @param DatagridMapper $datagridMapper
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('id')
            ->add('symbol')
            ->add('aliases')
            ->add('icon')
            ->add('dateAdded')
        ;
    }

    /**
     * @param ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('id')
            ->addIdentifier('symbol')
            ->add('aliases', null, [
                'template' => ':CRUD:list_array_with_delimeters.html.twig'
            ])
            ->add('icon', null, [
                'template' => ':CRUD:emoticon_field.html.twig'
            ])
            ->add('sortOrder')
            ->add('dateAdded')
            ->add('_action', 'actions', array(
                'actions' => array(
                    'show' => array(),
                    'edit' => array(),
                    'delete' => array(),
                )
            ))
        ;
    }

    /**
     * @param FormMapper $formMapper
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->with('Emoticon', ['class' => 'col-lg-6 col-md-12'])
                ->add('symbol')
                ->add('aliases', 'sonata_type_native_collection', [
                    'allow_add' => true,
                    'allow_delete' => true,
                    'required' => false,
                    'options' => [
                        'required' => false
                    ]
                ])
                ->add('iconFile', 'vich_image', [
                    'required' => false,
                    'label' => false,
                    'error_bubbling' => true,
                    'download_link' => false,
                    'allow_delete' => false
                ])
                ->add('sortOrder')
                ->add('dateAdded', 'sonata_type_datetime_picker', [
                    'format' => DateTimeServices::FORMAT_DATETIME_DOTTED
                ])
            ->end()
        ;
    }

    /**
     * @param ShowMapper $showMapper
     */
    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->add('id')
            ->add('symbol')
            ->add('aliases')
            ->add('icon')
            ->add('sortOrder')
            ->add('dateAdded')
            ->add('dateUpdated')
        ;
    }
}
