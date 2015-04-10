<?php

namespace AppBundle\Admin;

use AppBundle\Entity\ModelRequest;
use AppBundle\Tools\DateTimeServices;
use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Component\Form\Extension\Core\Type\DateType;

class ModelRequestAdmin extends Admin
{
    /**
     * @param DatagridMapper $datagridMapper
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('id')
            ->add('firstName')
            ->add('lastName')
            ->add('email')
            ->add('status')
            ->add('dateAdded')
        ;
    }

    /**
     * @param ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $actions = array(
            'show' => array(),
            'edit' => array(),
            'delete' => array(),
        );

        /** @var ModelRequest $req */
        if (($req = $this->getSubject()) && $req->getStatus() == ModelRequest::STATUS_NEW) {

        }

        $actions['Register Model'] = [
            'template' => ':CRUD:list__action_create_model_by_request.html.twig'
        ];

        $listMapper
            ->add('id')
            ->addIdentifier('firstName')
            ->addIdentifier('lastName')
            ->add('email')
            ->add('status')
            ->add('dateAdded')
            ->add('_action', 'actions', array(
                'actions' => $actions
            ))
        ;
    }

    /**
     * @param FormMapper $formMapper
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
           // ->tab('Model Request')
                ->with('Request', array('class' => 'col-lg-6 col-md-12'))->end();
//                ->with('Social', array('class' => 'col-md-6'))->end()
           // ->end();

        $formMapper
            //->tab('Model Request')
                ->with('Request')
                    ->add('status', 'choice', [
                        'choices' => ModelRequest::getStatusLabels(),
                        'required' => true
                    ])
                    ->add('firstName')
                    ->add('lastName')
                    ->add('email')
                    ->add('facebookURL', 'url', [
                        'label' => 'Facebook URL',
                        'required' => false
                    ])
                    ->add('instagramURL', 'url', [
                        'label' => 'Instagram URL',
                        'required' => false
                    ])
                    ->add('message')
                    ->add('model', 'sonata_type_model_reference', [
                        'model_manager' => $this->getModelManager(),
                        'read_only' => false,
                        'required' => false
                    ])
                    ->add('dateAdded', 'sonata_type_datetime_picker', [
                        'format' => DateTimeServices::FORMAT_DATETIME_DOTTED
                    ])
                    ->add('dateUpdated', 'sonata_type_datetime_picker', [
                        'format' => DateTimeServices::FORMAT_DATETIME_DOTTED,
                        'required' => false
                    ])
                ->end()
            //->end()
        ;
    }

    /**
     * @param ShowMapper $showMapper
     */
    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->add('id')
            ->add('firstName')
            ->add('lastName')
            ->add('email')
            ->add('facebookURL')
            ->add('instagramURL')
            ->add('message')
            ->add('dateAdded')
            ->add('dateUpdated')
        ;
    }

    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->add('createModelByRequest', $this->getRouterIdParameter().'/create-model');
    }
}
