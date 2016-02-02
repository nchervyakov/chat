<?php
/**
 * Created with IntelliJ IDEA by Nick Chervyakov.
 * User: Nikolay Chervyakov
 * Date: 01.02.2016
 * Time: 17:58
 */


namespace PaymentBundle;


use Symfony\Component\DependencyInjection\ContainerAware;

class CoinMoneyEstimator extends ContainerAware
{
    /**
     * @param float $amount Amount in USD
     * @return float Estimated coins
     */
    public function estimateCoins($amount)
    {
        $coinPrice = (float) $this->container->getParameter('payment.coin_price');

        $variants = $this->container->getParameter('payment.variants');
        $vCount = count($variants);

        if ($vCount === 0) {
            $variants = [0 => 0, 20 => round(20 / $coinPrice, 2)];

        } else if ($vCount === 1) {
            $variants[0] = 0;
        }

        ksort($variants);
        $v = [];
        $counter = 0;
        foreach ($variants as $x => $y) {
            $v[$counter] = [$x, $y];
            $counter++;
        }

        if ($amount < $v[0][0]) {
            return round($amount / $coinPrice, 2);
        }

        $m = ($v[1][1] - $v[0][1]) / ($v[1][0] - $v[0][0]);
        $b = $v[0][1] - $m * $v[0][0];

        return round($m * $amount + $b, 2);
    }
}