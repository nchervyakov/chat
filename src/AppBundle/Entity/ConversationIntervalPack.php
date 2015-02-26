<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ConversationIntervalPack
 *
 * @ORM\Table(name="conversation_interval_packs")
 * @ORM\Entity(repositoryClass="AppBundle\Entity\ConversationIntervalPackRepository")
 */
class ConversationIntervalPack
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;


    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }
}
