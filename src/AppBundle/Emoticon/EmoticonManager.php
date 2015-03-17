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

class EmoticonManager extends ContainerAware
{
    /**
     * @var Collection|Emoticon[]
     */
    private $emoticons;

    private $map;

    private $quotedMap;

    public function convertEmoticons($string)
    {
        $quotedMap = $this->getQuotedMap();
        $map = $this->getEmoticonMap();

        return preg_replace_callback(array_keys($quotedMap), function ($matches) use ($quotedMap, $map) {
            /** @var Emoticon $emoticon */
            $emoticon = $map[$matches[0]];
            return '<img src="/images/emoticons/' . $emoticon->getIcon() . '" alt="' . htmlspecialchars($matches[0], ENT_COMPAT, 'UTF-8') . '" />';
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