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
     * @throws \Exception
     * @throws NotEnoughMoneyException
     */
    public function payConversationInterval(ConversationInterval $interval)
    {
        $em = $this->getManager();

        try {
            $em->beginTransaction();
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

            $em->commit();

        } catch (\Exception $e) {
            //$em->rollback();
            throw $e;
        }

        $producer = $this->container->get('old_sound_rabbit_mq.notifications_producer');
        $producer->setContentType('application/json');

        try {
            if ($model->isOnline()) {
                $producer->publish(json_encode(['type' => 'coins-changed', 'data' => [
                    'coins' => number_format($model->getCoins(), 2, '.', '')
                ]]), 'user.' . $model->getId());
            }

            if ($client->isOnline()) {
                $producer->publish(json_encode(['type' => 'coins-changed', 'data' => [
                    'coins' => number_format($client->getCoins(), 2, '.', '')
                ]]), 'user.' . $client->getId());
            }

        } catch (\ErrorException $e){
            restore_error_handler();
            $this->container->get('logger')->addCritical($e->getMessage());
        }
    }

    /**
     * @return \Doctrine\Common\Persistence\ObjectManager|EntityManager
     */
    protected function getManager()
    {
        return $this->container->get('doctrine')->getManager();
    }
}