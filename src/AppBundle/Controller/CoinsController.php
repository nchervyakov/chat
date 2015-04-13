<?php
/**
 * Created with IntelliJ IDEA by Nick Chervyakov.
 * User: Nikolay Chervyakov 
 * Date: 13.04.2015
 * Time: 14:33
  */



namespace AppBundle\Controller;

use AppBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Class CoinsController
 * @package AppBundle\Controller
 * @Route("/coins")
 * @Security("has_role('ROLE_USER')")
 */
class CoinsController extends Controller
{
    /**
     * @Route("/add", name="coins_add", methods={"POST"})
     * @param Request $request
     * @return JsonResponse
     */
    public function addCoinsAction(Request $request)
    {
        $amount = (int) $request->request->get('amount', 0);

        if (!is_numeric($amount) || $amount <= 0) {
            throw new BadRequestHttpException("Invalid amount.");
        }

        /** @var User $user */
        $user = $this->getUser();

        $newCoins = $user->getCoins() + $amount;
        $user->setCoins($newCoins);
        $this->getDoctrine()->getManager()->flush();

        return new JsonResponse([
            'success' => true,
            'amount' => number_format($newCoins, 2, '.', '')
        ]);
    }
}