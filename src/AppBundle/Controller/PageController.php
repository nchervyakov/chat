<?php
/**
 * Created with IntelliJ IDEA by Nick Chervyakov.
 * User: Nikolay Chervyakov
 * Date: 04.02.2016
 * Time: 19:32
 */


namespace AppBundle\Controller;


use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Class PageController
 * @package AppBundle\Controller
 */
class PageController extends Controller
{
    /**
     * @Route("/about", name="page_about", methods={"GET"})
     */
    public function aboutAction()
    {
        return $this->render(':Page:about.html.twig');
    }
}