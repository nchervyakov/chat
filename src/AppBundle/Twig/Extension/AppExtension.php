<?php
/**
 * Created by IntelliJ IDEA.
 * User: Nikolay Chervyakov 
 * Date: 17.03.2015
 * Time: 17:39
 */


namespace AppBundle\Twig\Extension;



use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class AppExtension extends \Twig_Extension implements ContainerAwareInterface
{
    /**
     * @var Container
     */
    protected $container;

    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('emoticons', [$this, 'convertEmoticons'], [
                'is_safe' => ['html']
            ])
        ];
    }

    public function convertEmoticons($string)
    {
        return $this->container->get('app.emoticon_manager')->convertEmoticons($string);
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'app_extension';
    }

    /**
     * Sets the Container.
     *
     * @param ContainerInterface|null $container A ContainerInterface instance or null
     *
     * @api
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }
}