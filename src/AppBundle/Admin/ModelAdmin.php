<?php
/**
 * Created with IntelliJ IDEA by Nick Chervyakov.
 * User: Nikolay Chervyakov 
 * Date: 08.04.2015
 * Time: 15:16
  */



namespace AppBundle\Admin;


use Doctrine\ORM\QueryBuilder;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Form\FormMapper;

class ModelAdmin extends UserAdmin
{
    protected $baseRouteName = 'admin_app_model';

    protected $baseCodeRoute = "sonata.user.admin.model";

    protected $baseRoutePattern = "/app/model";

    protected $classnameLabel = 'model';

    protected function configureFormFields(FormMapper $formMapper)
    {
        parent::configureFormFields($formMapper);

        $formMapper
            ->remove('gender')
            ->remove('groups');

        $formMapper
            ->tab('Security')
                ->with('Groups', array('class' => 'col-md-4'))->end()
            ->end()
        ;
    }

    public function createQuery($context = 'list')
    {
        /** @var ProxyQueryInterface|QueryBuilder $query */
        $query = parent::createQuery($context);
        $query
            ->join('o.groups', 'g')
            ->andWhere('g.name LIKE :group_name')
            ->setParameter('group_name', 'Models');
        return $query;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        parent::configureListFields($listMapper);

        $listMapper
            ->remove('username')
            ->remove('email')
            ->addIdentifier('email');

        $listMapper->reorder(['id', 'email']);
    }
}