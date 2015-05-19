<?php
/**
 * Created with IntelliJ IDEA by Nick Chervyakov.
 * User: Nikolay Chervyakov 
 * Date: 30.03.2015
 * Time: 16:41
  */



namespace AppBundle\Admin;


use AppBundle\Entity\User;
use AppBundle\Tools\DateTimeServices;
use Knp\Menu\ItemInterface as MenuItemInterface;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\UserBundle\Admin\Entity\UserAdmin as BaseUserAdmin;

class UserAdmin extends BaseUserAdmin
{
    public function __construct($code, $class, $baseControllerName)
    {
        parent::__construct($code, $class, $baseControllerName);
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        //parent::configureFormFields($formMapper);

        // define group zoning
        $formMapper
            ->tab('User')
                ->with('Profile', array('class' => 'col-lg-6 col-md-12'))->end()
                ->with('General', array('class' => 'col-lg-6 col-md-12'))->end()
                ->with('Social IDs', array('class' => 'col-lg-6 col-md-12'))->end()
//                ->with('Social', array('class' => 'col-lg-6 col-md-12'))->end()
            ->end()
            ->tab('Security')
                ->with('Status', array('class' => 'col-md-4'))->end()
                ->with('Groups', array('class' => 'col-md-4'))->end()
                ->with('Keys', array('class' => 'col-md-4'))->end()
                ->with('Roles', array('class' => 'col-md-12'))->end()
            ->end()
        ;

        $now = new \DateTime();

        $formMapper
            ->tab('User')
                ->with('General')
                    ->add('username')
                    ->add('email')
                    ->add('plainPassword', 'text', array(
                        'required' => false //(!$this->getSubject() || is_null($this->getSubject()->getId()))
                    ))
                    ->add('dateAdded', 'sonata_type_datetime_picker', [
                        'format' => DateTimeServices::FORMAT_DATETIME_DOTTED
                    ])
                ->end()
                ->with('Profile')
                    ->add('dateOfBirth', 'sonata_type_date_picker', array(
                        'years' => range(1900, $now->format('Y')),
                        'dp_min_date' => '1900-01-01',
                        //'dp_max_date' => $now->format('Y-m-d'),
                        //'dp_default_date' => false,
                        //'dp_disabled_dates' => false,
                        //'dp_enabled_dates' => false,
                        'required' => false,
                        //'datepicker_use_button' => true
                        'format' => DateTimeServices::FORMAT_DATE_DOTTED //DateType::HTML5_FORMAT
                    ))
                    ->add('firstname', null, array('required' => false))
                    ->add('lastname', null, array('required' => false))
                    //->add('website', 'url', array('required' => false))
                    //->add('biography', 'text', array('required' => false))
                    ->add('gender', 'sonata_user_gender', array(
                        'required' => true,
                        'translation_domain' => $this->getTranslationDomain()
                    ))
                    //->add('locale', 'locale', array('required' => false))
                    //->add('timezone', 'timezone', array('required' => false))
                    ->add('phone', null, array('required' => false))
                ->end()
                ->with('Social IDs')
                    ->add('facebookId', null, ['label' => 'form.label_facebook_id'])
                    ->add('twitterId', null, ['label' => 'form.label_twitter_id'])
                    ->add('instagramId', null, ['label' => 'form.label_instagram_id'])
                    ->add('facebookURL', null, ['label' => 'form.label_facebook_u_r_l'])
                    ->add('instagramURL', null, ['label' => 'form.label_instagram_u_r_l'])
                ->end()
//                ->with('Social')
//                    ->add('facebookUid', null, array('required' => false))
//                    ->add('facebookName', null, array('required' => false))
//                    ->add('twitterUid', null, array('required' => false))
//                    ->add('twitterName', null, array('required' => false))
//                    ->add('gplusUid', null, array('required' => false))
//                    ->add('gplusName', null, array('required' => false))
//                ->end()
            ->end()
        ;

        if ($this->getSubject() && !$this->getSubject()->hasRole('ROLE_SUPER_ADMIN')) {
            $formMapper
                ->tab('Security')
                    ->with('Status')
                        ->add('locked', null, array('required' => false))
                        ->add('expired', null, array('required' => false))
                        ->add('enabled', null, array('required' => false))
                        ->add('credentialsExpired', null, array('required' => false))
                        ->add('activated', null, ['required' => false])
                    ->end()
                    ->with('Groups')
                        ->add('groups', 'sonata_type_model', array(
                            'required' => false,
                            'expanded' => true,
                            'multiple' => true
                        ))
                    ->end()
                    ->with('Roles')
                        ->add('realRoles', 'sonata_security_roles', array(
                            'label'    => 'form.label_roles',
                            'expanded' => true,
                            'multiple' => true,
                            'required' => false
                        ))
                    ->end()
                ->end()
            ;

            $formMapper
                ->tab('Security')
                    ->with('Keys')
//                      ->add('token', null, array('required' => false))
//                      ->add('twoStepVerificationCode', null, array('required' => false))
                        ->add('activationToken', null, ['required' => false])
                    ->end()
                ->end()
            ;
        }
    }

    protected function configureDatagridFilters(DatagridMapper $filterMapper)
    {
        //parent::configureDatagridFilters($filterMapper);
        $filterMapper
            ->add('id')
            ->add('username')
            ->add('locked')
            ->add('activated')
            ->add('email')
            ->add('firstname')
            ->add('lastname')
            ->add('groups')
        ;
    }

    protected function configureRoutes(RouteCollection $collection)
    {
        parent::configureRoutes($collection);

        //$collection->add('sendModelNotification', $this->getRouterIdParameter() . '/send-model-notification');
        $collection->add('activate', $this->getRouterIdParameter() . '/activate');
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        //parent::configureListFields($listMapper);
        $listMapper
            //->add('thumbnail.fileName')
            ->addIdentifier('id')
            ->addIdentifier('username')
            ->add('email')
            ->add('fullName')
            ->add('groups')
            ->add('enabled', null, array('editable' => true))
            ->add('activated', null, array('editable' => true))
            ->add('locked', null, array('editable' => true))
            ->add('dateAdded', 'date')
            ->add('_action', 'actions', [
                'actions' => [
                    'activate' => ['template' => ':CRUD:list__action_activate_model.html.twig']
                ]
            ]);
        ;

        if ($this->isGranted('ROLE_ALLOWED_TO_SWITCH')) {
            $listMapper
                ->add('impersonating', 'string', array('template' => 'SonataUserBundle:Admin:Field/impersonating.html.twig'))
            ;
        }
    }

    public function getFormBuilder()
    {
        $this->formOptions['data_class'] = $this->getClass();

        $options = $this->formOptions;
        $options['validation_groups'] = (!$this->getSubject() || is_null($this->getSubject()->getId())) ? 'AppRegistration' : 'AppProfile';

        $formBuilder = $this->getFormContractor()->getFormBuilder( $this->getUniqid(), $options);

        $this->defineFormBuilder($formBuilder);

        return $formBuilder;
    }

    /**
     * @param mixed|User $user
     * @return mixed|void
     */
    public function preUpdate($user)
    {
        if ($user->getPassword() === null) {
            $user->setPlainPassword('');
            $user->setPassword('');
        }
        parent::preUpdate($user);
    }

    /**
     * @param mixed|User $user
     * @return mixed|void
     */
    public function prePersist($user)
    {
        if ($user) {
            if ($user->getPassword() === null) {
                $user->setPlainPassword('');
                $user->setPassword('');
            }
        }
        parent::prePersist($user);
    }

    /**
     * @param mixed|User $user
     * @return mixed|void
     */
    public function postPersist($user)
    {
        parent::postPersist($user);
    }

    protected function configureShowFields(ShowMapper $showMapper)
    {
        //parent::configureShowFields($showMapper);
        $showMapper
            ->with('General')
                ->add('username')
                ->add('email')
                ->add('activated')
                ->add('enabled')
            ->end()
            ->with('Groups')
                ->add('groups')
            ->end()
            ->with('Profile')
                ->add('dateOfBirth', 'date')
                ->add('firstname')
                ->add('lastname')
//                ->add('website')
//                ->add('biography')
                ->add('genderLabel', 'trans', ['label' => 'Gender', 'catalogue' => 'messages'])
//                ->add('locale')
//                ->add('timezone')
                ->add('phone')
            ->end()
//            ->with('Social')
//                ->add('facebookUid')
//                ->add('facebookName')
//                ->add('twitterUid')
//                ->add('twitterName')
//                ->add('gplusUid')
//                ->add('gplusName')
//            ->end()
            ->with('Social IDs')
                ->add('facebookId')
                ->add('twitterId')
                ->add('instagramId')
            ->end()
            ->with('Security')
                ->add('token')
                ->add('twoStepVerificationCode')
            ->end()
        ;
    }

    protected function configureTabMenu(MenuItemInterface $menu, $action, AdminInterface $childAdmin = null)
    {
        parent::configureTabMenu($menu, $action, $childAdmin);
//        if (($user = $this->getSubject()) && $user->hasRole('ROLE_MODEL')) {
//            $menu->addChild('mmm', [
//                'route' => 'chat',
//                'label' => 'Hello',
//                'attributes' => ['title' => ('chat'), 'class' => 'btn btn-primary']
//            ]);
//        }
    }
}