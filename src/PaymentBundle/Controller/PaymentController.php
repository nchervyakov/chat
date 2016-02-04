<?php
/**
 * Created with IntelliJ IDEA by Nick Chervyakov.
 * User: Nikolay Chervyakov
 * Date: 29.01.2016
 * Time: 16:12
 */


namespace PaymentBundle\Controller;


use AppBundle\Entity\User;
use Doctrine\DBAL\Driver\Connection;
use PaymentBundle\Entity\AbstractOrder;
use PaymentBundle\Entity\CoinOrder;
use PaymentBundle\Entity\Payment;
use PaymentBundle\Event\OrderPayedEvent;
use PaymentBundle\PaymentEvents;
use Payum\Bundle\PayumBundle\Controller\PayumController;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\GetHumanStatus;
use Payum\Core\Request\Sync;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class PaymentController
 * @package PaymentBundle\Controller
 *
 * @Security("has_role('ROLE_USER')")
 */
class PaymentController extends PayumController
{
    /**
     * @param Request $request
     * @Route("/buy-coins", name="payments_prepare_coin")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function prepareCoinAction(Request $request)
    {
        $form = $this->createPaymentForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            $data = $form->getData();
            $amount = $data['amount'] === 'custom' ? $data['custom'] : $data['amount'];

            $estimatedCoins = round($this->get('payment.coin_money_estimator')->estimateCoins($amount), 2);
            /** @var User $user */
            $user = $this->getUser();

            $order = new CoinOrder();
            $order->setCoins($estimatedCoins);
            $order->setAmount($amount);
            $order->setUser($user);
            $em->persist($order);
            $em->flush();

            $payment = new Payment();
            $payment->setCurrencyCode('USD');
            $payment->setTotalAmount($amount * 100);
            $payment->setClientId($user->getId());
            $payment->setClientEmail($user->getEmail());
            $payment->setNumber($order->getId());
            $payment->setDescription("Purchase $estimatedCoins coins");

            $details = [
                'REQCONFIRMSHIPPING' => 0,
                'NOSHIPPING' => 1,              // We do not need shipping
            ];

            $payment->setDetails($details);

            $storage = $this->getPayum()->getStorage($payment);
            $storage->update($payment);

            $order->setPayment($payment);
            $em->flush();

            $captureToken = $this->getTokenFactory()->createCaptureToken(
                'paypal_express_checkout_with_ipn_enabled',
                $payment,
                'payments_done'
            );

            return $this->redirect($captureToken->getTargetUrl());
        }

        return $this->render(':Payment:payment.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @param $request
     * @return JsonResponse
     * @Route("/done", name="payments_done", methods={"GET"})
     */
    public function doneAction(Request $request)
    {
        $token = $this->getHttpRequestVerifier()->verify($request);
        $gateway = $this->getPayum()->getGateway($token->getGatewayName());

        try {
            $gateway->execute(new Sync($token));
        } catch (RequestNotSupportedException $e) {}

        $gateway->execute($status = new GetHumanStatus($token));
        /** @var Payment $payment */
        $payment = $status->getFirstModel();

        if (!$payment) {
            throw new NotFoundHttpException('Unknown or missing payment token');
        }

        $order = $payment->getOrder();
        /** @var User $user */
        $user = $this->getUser();

        if ($order->getStatus() === AbstractOrder::STATUS_PAYED) {
            return $this->render(':Payment:done.html.twig', [
                'payment' => $payment,
                'remainingCoins' => $user->getCoins(),
                'status' => $status,
            ]);
        }

        $formView = null;

        if ($status->isCaptured()) {
            $em = $this->getDoctrine()->getManager();

            /** @var Connection $conn */
            $conn = $this->getDoctrine()->getConnection();

            try {
                $conn->beginTransaction();

                $user->addCoins($order->getCoins());

                $order->setStatus(AbstractOrder::STATUS_PAYED);
                $em->flush();
                $conn->commit();

            } catch (\Exception $e) {
                $conn->rollBack();
                throw new HttpException(500, 'Error while writing data to DB.');
            }

            $this->get('event_dispatcher')->dispatch(PaymentEvents::ORDER_PAYED, new OrderPayedEvent($order, $payment));
            //$this->getHttpRequestVerifier()->invalidate($token);

        } else {
            $this->get('session')->getFlashBag()
                ->add('warning', 'Error while payment. Please try again.');
            $formView = $this->createPaymentForm()->createView();
        }

        return $this->render(':Payment:done.html.twig', [
            'status' => $status,
            'payment' => $payment,
            'form' => $formView,
            'remainingCoins' => $user->getCoins()
        ]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @Route("/estimate-coins", name="payments_estimate_coins", methods={"GET"})
     */
    public function estimateAction(Request $request)
    {
        $amount = $request->query->get('amount');

        if (!$amount || !is_numeric($amount)) {
            $coins = '';
        } else {
            $coins = $this->get('payment.coin_money_estimator')->estimateCoins($amount);
        }

        return new JsonResponse(['coins' => $coins]);
    }

    protected function createPaymentForm()
    {
        $form = $this->createForm('payment_selection', ['amount' => 20, 'custom' => 20], [
            'method' => 'POST',
            'action' => $this->generateUrl('payments_prepare_coin')
        ]);

        $form->add('submit', 'submit');

        return $form;
    }
}