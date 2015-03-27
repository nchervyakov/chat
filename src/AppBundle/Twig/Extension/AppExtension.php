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
            ]),
            new \Twig_SimpleFilter('date_interval', [$this, 'dateInterval'])
        ];
    }

    public function convertEmoticons($string)
    {
        return $this->container->get('app.emoticon_manager')->convertEmoticons($string);
    }

    public function dateInterval($date)
    {
        $env = $this->container->get('twig');
        /** @var \Twig_Extension_Core $core */
        $core = $env->getExtension('core');

        $formats = $core->getDateFormat();
        $format = $date instanceof \DateInterval ? $formats[1] : $formats[0];

        if (is_numeric($date)) {
            $now = new \DateTime();
            $before = new \DateTime('-' . $date . ' seconds');
            $date = $now->diff($before);
        }

        if ($date instanceof \DateInterval) {

            if ($date->days > 0) {
                $format = '%a days %H:%I:%S';
            } else if ($date->h > 0) {
                $format = '%H:%I:%S';
            } else {
                $format = '%I:%S';
            }

            return $date->format($format);
        }

        return twig_date_converter($this->container->get('twig'), $date, null)->format($format);
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