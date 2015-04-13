<?php
/**
 * Created with IntelliJ IDEA by Nick Chervyakov.
 * User: Nikolay Chervyakov 
 * Date: 13.04.2015
 * Time: 17:41
  */



namespace AppBundle\Coins;


use AppBundle\Entity\CoinTransaction;
use AppBundle\Entity\ConversationInterval;
use AppBundle\Exception\NotEnoughMoneyException;
use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\ContainerAware;

class CoinsService extends ContainerAware
{
    /**
     * @param ConversationInterval $interval
     * @throws NotEnoughMoneyException
     */
    public function payConversationInterval(ConversationInterval $interval)
    {
        $em = $this->getManager();

        $conversation = $interval->getConversation();
        $client = $conversation->getClient();
        $model = $conversation->getModel();

        $clientTransaction = new CoinTransaction();
        $em->persist($clientTransaction);
        $clientTransaction->setAmount($interval->getPrice());
        $clientTransaction->setSource($conversation->getClient());
        $newClientCoins = (double)$client->getCoins() - (double)$interval->getPrice();
        if ($newClientCoins < 0) {
            throw new NotEnoughMoneyException();
        }
        $client->setCoins($newClientCoins);

        $modelTransaction = new CoinTransaction();
        $em->persist($modelTransaction);
        $modelTransaction->setAmount($interval->getModelEarnings());
        $modelTransaction->setTarget($conversation->getModel());
        $model->setCoins((double)$model->getCoins() + (double)$interval->getModelEarnings());

        $interval->setStatus(ConversationInterval::STATUS_PAYED);

        $em->flush();
    }

    /**
     * @return \Doctrine\Common\Persistence\ObjectManager|EntityManager
     */
    protected function getManager()
    {
        return $this->container->get('doctrine')->getManager();
    }
}