<?php

namespace AppBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * QueueMessageRepository
 *
 */
class QueueMessageRepository extends EntityRepository
{
    /**
     * @param User $user
     * @return array
     */
    public function getUserNewMessages(User $user)
    {
        $em = $this->getEntityManager();
        /** @var QueueMessage[]|array $messages */
        $messages = $this->createQueryBuilder('qm')
            ->where('qm.targetUser = :user')
            ->orderBy('qm.dateAdded', 'ASC')
            ->setParameter('user', $user)
            ->getQuery()->execute();

        $result = [];
//        $toRemove = [];
//        $targetDate = new \DateTime();
//        $targetDate->modify('-15 minutes');

//        foreach ($messages as $message) {
//            if ($targetDate->diff($message->getDateAdded())->invert) {
//                $toRemove[] = $message;
//            } else {
//                $result[] = $message;
//            }
//        }

        foreach ($messages as $message) {
            $em->remove($message);
            $result[] = $message;
        }

        $em->flush();

        return $result;
    }
}
