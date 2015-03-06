<?php
/**
 * Created by IntelliJ IDEA.
 * User: Nikolay Chervyakov 
 * Date: 10.06.2014
 * Time: 11:31
 */


namespace AppBundle\Menu;


use Knp\Menu\FactoryInterface;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpFoundation\Request;

class MenuBuilder extends ContainerAware
{
    /** @var \Knp\Menu\FactoryInterface */
    private $factory;

    public function __construct(FactoryInterface $factory, array $options = [])
    {
        $this->factory = $factory;
    }

    public function createMainMenu(Request $request, array $options = [])
    {
        $options = array_merge([
            'is_main' => true
        ], $options);

        $menu = $this->factory->createItem('root');
        $menu->setChildrenAttribute('class', 'nav navbar-nav');


        $translator = $this->container->get('translator');

//        if ($options['is_main'] !== false) {
//            $menu->addChild('chat_with_models', [
//                'route' => 'homepage',
//                'label' => $translator->trans('menu.main.home'),
//                'attributes' => ['title' => $translator->trans('menu.go_to_homepage')]
//            ]);
//        }

        if ($this->container->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            $menu->addChild('menu.main.search', [
                'route' => 'search_index',
                'label' => $translator->trans('menu.main.search'),
                'attributes' => ['title' => $translator->trans('menu.main.search')]
            ]);

//            $menu->addChild('menu.main.logout', [
//                'route' => 'fos_user_security_logout',
//                'label' => $translator->trans('menu.main.logout'),
//                'attributes' => ['title' => $translator->trans('menu.main.logout')]
//            ]);
        }

        return $menu;
    }
}