<?php
/**
 * Created with IntelliJ IDEA by Nick Chervyakov.
 * User: Nikolay Chervyakov
 * Date: 29.01.2016
 * Time: 16:12
 */


namespace PaymentBundle\Controller;


use Payum\Core\Request\GetHumanStatus;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class PaymentController
 * @package PaymentBundle\Controller
 */
class PaymentController extends Controller
{
    /**
     * @param Request $request
     * @Route("/prepare-coin", name="payments_prepare_coin")
     */
    public function prepareCoinAction(Request $request)
    {

    }

    /**
     * @param $request
     * @return JsonResponse
     * @Route("/done", name="payments_done")
     */
    public function doneAction($request)
    {
        $token = $this->get('payum.security.http_request_verifier')->verify($request);

        $gateway = $this->get('payum')->getGateway($token->getGatewayName());

        // you can invalidate the token. The url could not be requested any more.
        // $this->get('payum.security.http_request_verifier')->invalidate($token);

        // Once you have token you can get the model from the storage directly.
        //$identity = $token->getDetails();
        //$payment = $payum->getStorage($identity->getClass())->find($identity);

        // or Payum can fetch the model for you while executing a request (Preferred).
        $gateway->execute($status = new GetHumanStatus($token));
        $payment = $status->getFirstModel();

        // you have order and payment status
        // so you can do whatever you want for example you can just print status and payment details.

        return new JsonResponse(array(
            'status' => $status->getValue(),
            'payment' => array(
                'total_amount' => $payment->getTotalAmount(),
                'currency_code' => $payment->getCurrencyCode(),
                'details' => $payment->getDetails(),
            ),
        ));
    }
}