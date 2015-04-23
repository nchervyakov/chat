<?php
/**
 * Created with IntelliJ IDEA by Nick Chervyakov.
 * User: Nikolay Chervyakov 
 * Date: 16.04.2015
 * Time: 15:26
  */



namespace AppBundle\Uploader\Namer;


use Cocur\Slugify\Slugify;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Vich\UploaderBundle\Mapping\PropertyMapping;
use Vich\UploaderBundle\Naming\NamerInterface;

class SluggedOrignameNamer implements NamerInterface
{
    /**
     * @var Slugify
     */
    protected $slugify;

    public function __construct(Slugify $slugify)
    {
        $this->slugify = $slugify;
    }

    /**
     * {@inheritDoc}
     */
    public function name($object, PropertyMapping $mapping)
    {
        /** @var $file UploadedFile */
        $file = $mapping->getFile($object);
        $originalName = $file->getClientOriginalName();
        $pathinfo = pathinfo($originalName);
        mt_srand();
        return uniqid().'_'.$this->slugify->slugify($pathinfo['filename']) . '.' . mb_strtolower($pathinfo['extension'], 'utf-8');
    }
}