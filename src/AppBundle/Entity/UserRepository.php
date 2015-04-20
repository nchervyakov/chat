<?php

namespace AppBundle\Entity;

use AppBundle\Model\ModelSearch;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;

/**
 * UserRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class UserRepository extends EntityRepository
{
    /**
     * Populates model search query builder based on ModelSearch instance.
     * @param ModelSearch $search
     * @param int $onlineMinutes
     * param boolean $searchOnline Whether to search online or offline models
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function prepareQueryBuilderForModelSearch(ModelSearch $search, $onlineMinutes = null/*, $searchOnline = true*/)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();

        $qb->select('u')
            ->from('AppBundle:User', 'u')
            ->join('u.groups', 'g')
            ->where('g.name LIKE :models_group')->setParameter('models_group', 'Models')
            //->where('u.gender LIKE :gender')->setParameter('gender', User::GENDER_FEMALE)
            ->orderBy('u.order', 'DESC');

//        if ($search->getFrom() !== null) {
//            $fromDate = new \DateTime('-' . $search->getFrom() . ' years');
//            $qb->andWhere('u.dateOfBirth <= :from')->setParameter('from', $fromDate);
//        }
//
//        if ($search->getTo() !== null) {
//            $toDate = new \DateTime('-' . $search->getTo() . ' years');
//            $qb->andWhere('u.dateOfBirth >= :to')->setParameter('to', $toDate);
//        }

//        if ($search->isWithPhoto()) {
//            $qb->innerJoin('u.thumbnail', 't')
//                ->andWhere('t.fileName != :thumbnail')
//                ->setParameter('thumbnail', '');
//        }

        if ($search->getName()) {
            $parts = preg_split('/\s+/', $search->getName(), -1, PREG_SPLIT_NO_EMPTY);
            $part1 = array_shift($parts);
            $part2 = implode(' ', $parts);

            if ($part1) {
                if ($part2) {
                    $qb->andWhere(
                        $qb->expr()->orX(
                            $qb->expr()->andX('u.firstname LIKE :part1', 'u.lastname LIKE :part2'),
                            $qb->expr()->andX('u.firstname LIKE :part2', 'u.lastname LIKE :part1')
                        ))
                        ->setParameter('part1', $part1.'%')
                        ->setParameter('part2', $part2.'%');

                } else {
                    $qb->andWhere($qb->expr()->orX('u.firstname LIKE :part1', 'u.lastname LIKE :part1'))
                        ->setParameter('part1', $part1.'%');
                }
            }
        }

        if (is_numeric($onlineMinutes) && $onlineMinutes >= 0) {
            $onlineMinutes = (int) $onlineMinutes;
            $onlineDatetime = new \DateTime('-' . $onlineMinutes . ' minutes');

            if ($search->isOffline()) {
                $qb->andWhere(
                    $qb->expr()->orX('u.lastVisitedDate < :online_date', 'u.lastVisitedDate IS NULL')
                );
            } else {
                $qb->andWhere('u.lastVisitedDate >= :online_date');
            }
            $qb->setParameter('online_date', $onlineDatetime);
        }

        return $qb;
    }

    /**
     * @param User $user
     * @param User $companion
     * @param bool $withConversations
     * @return User[]
     */
    public function findUserCompanions(User $user, User $companion = null, $withConversations = false)
    {
        $qb = $this->createUserFriendsQueryBuilder($user, $companion);

        if ($withConversations) {
            $qb->addSelect('c');
        }

        /** @var User[] $result */
        $result = $qb->getQuery()->execute();
        foreach ($result as $key => $item) {
            if ($item->getId() == $user->getId()) {
                unset($result[$key]);
                //break;
            }
        }
        return $result;
    }

    /**
     * @param User $user
     * @param User $companion
     * @param bool $withUsers
     * @return User[]
     */
    public function findUserConversations(User $user, User $companion = null, $withUsers = false)
    {
        $qb = $this->createUserConversationsQueryBuilder($user, $companion, $withUsers);

        if ($withUsers) {
            $qb->addSelect('client')
                ->addSelect('model');
        }

        /** @var Conversation[] $result */
        $result = $qb->getQuery()->execute();

        return $result;
    }

    /**
     * @param User $user
     * @param User $companion
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function createUserFriendsQueryBuilder(User $user, User $companion = null)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();

        if ($user->hasRole('ROLE_CLIENT')) {
            list($field, $oppositeField) = ['c.model', 'c.client'];

        } else {
            list($field, $oppositeField) = ['c.client', 'c.model'];
        }

        $qb->select('u')
            ->from('AppBundle:Conversation', 'c')
            ->join('AppBundle:User', 'u', Join::WITH, $field . ' = u')
            ->where($oppositeField . ' = :user')
            ->orderBy('c.lastMessageDate', 'DESC')
            ->setParameter('user', $user);

        if ($companion) {
            $qb->andWhere($field . ' != :companion')->setParameter('companion', $companion);
        }

        return $qb;
    }

    /**
     * @param User $user
     * @param User $companion
     * @param bool $withUsers
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function createUserConversationsQueryBuilder(User $user, User $companion = null, $withUsers = false)
    {
        $field = $user->hasRole('ROLE_CLIENT') ? 'c.client' : 'c.model';

        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('c')
            ->from('AppBundle\\Entity\\Conversation', 'c')
            ->where($field . ' = :user')
            ->orderBy('c.lastMessageDate', 'DESC')
            ->setParameter('user', $user)
        ;

        if ($withUsers) {
            $qb->join('c.client', 'client');
            $qb->join('c.model', 'model');
        }

        if ($companion) {
            $qb->andWhere($field . ' != :companion')->setParameter('companion', $companion);
        }
        return $qb;
    }
}
