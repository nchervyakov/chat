<?php

namespace AppBundle\Admin;

use AppBundle\Tools\DateTimeServices;
use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Show\ShowMapper;

class ConversationAdmin extends Admin
{
    /**
     * @param DatagridMapper $datagridMapper
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('id')
            ->add('lastMessageDate')
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
            ->add('client')
            ->add('model')
            ->add('price')
            ->add('seconds')
            ->add('lastMessageDate')
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
            ->with('Conversation', ['class' => 'col-lg-6 col-md-12'])->end();

        $formMapper
            ->with('Conversation')
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
            ->add('client')
            ->add('model')
            ->add('price')
            ->add('modelEarnings')
            ->add('seconds')
            ->add('dateAdded')
            ->add('dateUpdated')
        ;
    }

    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->remove('create');
    }
}
