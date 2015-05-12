<?php

namespace AppBundle\Admin;

use AppBundle\Entity\MessageComplaint;
use AppBundle\Entity\User;
use AppBundle\Tools\DateTimeServices;
use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerInterface;

class MessageComplaintAdmin extends Admin
{
    protected $datagridValues = array(
        '_page'       => 1,
        '_per_page'   => 32,
        '_sort_order' => 'DESC',
        '_sort_by'    => 'dateAdded'
    );

    /**
     * @var Container
     */
    protected $container;

    public function getOpenComplaintsCount()
    {
        return $this->container->get('app.complaints')->getOpenComplaintsCount();
    }

    /**
     * @param DatagridMapper $datagridMapper
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $converter = function (User $user/*, $fields = []*/) {
            return $user->getEmail() . ' (' . $user->getFullName() . ')' ;
        };

        $datagridMapper
            ->add('id')
            ->add('message.conversation.client', 'doctrine_orm_model_autocomplete',
                [
                    'label' => 'Client',
                ], null, [
                    'property' => ['firstname', 'lastname', 'username', 'email'],
                    'to_string_callback' => $converter
                ]
            )
            ->add('message.conversation.model', 'doctrine_orm_model_autocomplete',
                [
                    'label' => 'Model',
                ], null, [
                    'property' => ['firstname', 'lastname', 'username', 'email'],
                    'to_string_callback' => $converter
                ]
            )
            ->add('status', null, [], 'choice', [
                'choices' => array_combine(MessageComplaint::getStatuses(), MessageComplaint::getStatuses())
            ])
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
            ->add('message.conversation.client', null, [
                'associated_property' => 'fullName',
                'label' => 'Client'
            ])
            ->add('message.conversation.model', null, [
                'associated_property' => 'fullName',
                'label' => 'Model'
            ])
            ->add('status', null, [
                'template' => ':CRUD:list_complaint_status.html.twig'
            ])
            ->add('message.content', null, [
                'label' => 'Message',
                'template' => ':CRUD:list_message_content.html.twig'
            ])
//            ->add('aliases', null, [
//                'template' => ':CRUD:list_array_with_delimeters.html.twig'
//            ])
//            ->add('icon', null, [
//                'template' => ':CRUD:emoticon_field.html.twig'
//            ])
//            ->add('sortOrder')
            ->add('dateAdded')
            ->add('_action', 'actions', array(
                'actions' => array(
//                    'show' => array(),
//                    'edit' => array(),
//                    'delete' => array(),
                    'accept' => ['template' => ':CRUD:list__action_accept_complaint.html.twig'],
                    'reject' => ['template' => ':CRUD:list__action_reject_complaint.html.twig'],
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
            ->with('Message Complaint', ['class' => 'col-lg-6 col-md-12'])
                ->add('status', 'choice', [
                    'choices' => array_combine(MessageComplaint::getStatuses(), MessageComplaint::getStatuses())
                ])
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
            ->add('message.conversation.client', null, [
                'label' => 'Client'
            ])
            ->add('message.conversation.model', null, [
                'label' => 'Model'
            ])
            ->add('status')
            ->add('dateAdded')
            ->add('dateUpdated')
        ;
    }

    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->add('accept', $this->getRouterIdParameter().'/accept');
        $collection->add('reject', $this->getRouterIdParameter().'/reject');
        $collection->remove('create');
    }

    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;
    }
}

