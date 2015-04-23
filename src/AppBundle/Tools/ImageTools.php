<?php
/**
 * Created with IntelliJ IDEA by Nick Chervyakov.
 * User: Nikolay Chervyakov 
 * Date: 23.04.2015
 * Time: 16:21
  */



namespace AppBundle\Tools;


use Imagine\Image\ImagineInterface;
use Symfony\Component\HttpFoundation\File\File;

/**
 * Various image manipulation tools
 * @package AppBundle\Tools
 */
class ImageTools
{
    /**
     * @var ImagineInterface
     */
    protected $imagine;

    /**
     * ImageTools constructor.
     * @param ImagineInterface $imagine
     */
    public function __construct(ImagineInterface $imagine)
    {
        $this->imagine = $imagine;
    }

    public function fixOrientation($file)
    {
        if ($file instanceof File) {
            $path = $file->getRealPath();
        } else {
            $path = $file;
        }

        try {
            $image = $this->imagine->open($path);
            $metadata = $image->metadata();
            $orientation = (int)$metadata['ifd0.Orientation'];

            if ($orientation != 1) {
                if ($orientation == 6) {
                    $image->rotate(90);
                } else if ($orientation == 8) {
                    $image->rotate(-90);
                } else if ($orientation == 3) {
                    $image->rotate(180);
                }

                $image->save($path);
            }
        } catch (\Exception $ex) {}

        return $file;
    }
}