<?php
/**
 * Created by IntelliJ IDEA.
 * User: Nikolay Chervyakov 
 * Date: 17.03.2015
 * Time: 17:49
 */


namespace AppBundle\Emoticon;


use AppBundle\Entity\Emoticon;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\Routing\Generator\UrlGenerator;

class EmoticonManager extends ContainerAware
{
    /**
     * @var Collection|Emoticon[]
     */
    private $emoticons;

    private $map;

    private $quotedMap;

    public function convertEmoticons($string, $urlType = UrlGenerator::ABSOLUTE_PATH)
    {
        if ($urlType == UrlGenerator::ABSOLUTE_URL) {
            $request = $this->container->get('request_stack')->getMasterRequest();
            if ($request) {
                $baseUrl = $request->getSchemeAndHttpHost();
            } else {
                $baseUrl = rtrim($this->container->getParameter('website'), '/ ');
            }
        } else {
            $baseUrl = '';
        }

        $quotedMap = $this->getQuotedMap();
        $map = $this->getEmoticonMap();

        return preg_replace_callback(array_keys($quotedMap), function ($matches) use ($quotedMap, $map, $baseUrl) {
            /** @var Emoticon $emoticon */
            $emoticon = $map[$matches[0]];
            return '<img src="' . $baseUrl . '/images/emoticons/' . $emoticon->getIcon() . '" alt="" />';
        }, $string);
    }

    public function getEmoticons()
    {
        if (!$this->emoticons) {
            $repo = $this->container->get('doctrine')->getRepository('AppBundle:Emoticon');
            $this->emoticons = $repo->findAll();
        }

        return $this->emoticons;
    }

    public function getEmoticonMap()
    {
        if ($this->map) {
            return $this->map;
        }

        $this->map = [];
        foreach ($this->getEmoticons() as $emoticon) {
            $this->map[$emoticon->getSymbol()] = $emoticon;

            if (!$emoticon->getAliases()) {
                continue;
            }
            foreach ($emoticon->getAliases() as $alias) {
                $this->map[$alias] = $emoticon;
            }
        }

        return $this->map;
    }

    public function getQuotedMap()
    {
        if ($this->quotedMap) {
            return $this->quotedMap;
        }

        $map = $this->getEmoticonMap();
        $this->quotedMap = [];
        foreach ($map as $alias => $emoticon) {
            $this->quotedMap['/' . preg_quote($alias, '/') . '/'] = $emoticon;
        }

        return $this->quotedMap;
    }
}