<?php

namespace Wallabag\CoreBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;

class EntryRepository extends EntityRepository
{
    /**
     * Retrieves unread entries for a user.
     *
     * @param int $userId
     * @param int $firstResult
     * @param int $maxResults
     *
     * @return Paginator
     */
    public function findUnreadByUser($userId, $firstResult, $maxResults = 12)
    {
        $qb = $this->createQueryBuilder('e')
            ->setFirstResult($firstResult)
            ->setMaxResults($maxResults)
            ->leftJoin('e.user', 'u')
            ->where('e.isArchived = false')
            ->andWhere('u.id =:userId')->setParameter('userId', $userId)
            ->orderBy('e.id', 'desc')
            ->getQuery();

        $paginator = new Paginator($qb);

        return $paginator;
    }

    /**
     * Retrieves read entries for a user.
     *
     * @param int $userId
     * @param int $firstResult
     * @param int $maxResults
     *
     * @return Paginator
     */
    public function findArchiveByUser($userId, $firstResult, $maxResults = 12)
    {
        $qb = $this->createQueryBuilder('e')
            ->select('e')
            ->setFirstResult($firstResult)
            ->setMaxResults($maxResults)
            ->leftJoin('e.user', 'u')
            ->where('e.isArchived = true')
            ->andWhere('u.id =:userId')->setParameter('userId', $userId)
            ->orderBy('e.id', 'desc')
            ->getQuery();

        $paginator = new Paginator($qb);

        return $paginator;
    }

    /**
     * Retrieves starred entries for a user.
     *
     * @param int $userId
     * @param int $firstResult
     * @param int $maxResults
     *
     * @return Paginator
     */
    public function findStarredByUser($userId, $firstResult, $maxResults = 12)
    {
        $qb = $this->createQueryBuilder('e')
            ->select('e')
            ->setFirstResult($firstResult)
            ->setMaxResults($maxResults)
            ->leftJoin('e.user', 'u')
            ->where('e.isStarred = true')
            ->andWhere('u.id =:userId')->setParameter('userId', $userId)
            ->orderBy('e.id', 'desc')
            ->getQuery();

        $paginator = new Paginator($qb);

        return $paginator;
    }

    /**
     * Find Entries.
     *
     * @param int    $userId
     * @param bool   $isArchived
     * @param bool   $isStarred
     * @param string $sort
     * @param string $order
     *
     * @return array
     */
    public function findEntries($userId, $isArchived = null, $isStarred = null, $sort = 'created', $order = 'ASC')
    {
        $qb = $this->createQueryBuilder('e')
            ->where('e.user =:userId')->setParameter('userId', $userId);

        if (null !== $isArchived) {
            $qb->andWhere('e.isArchived =:isArchived')->setParameter('isArchived', (bool) $isArchived);
        }

        if (null !== $isStarred) {
            $qb->andWhere('e.isStarred =:isStarred')->setParameter('isStarred', (bool) $isStarred);
        }

        if ('created' === $sort) {
            $qb->orderBy('e.id', $order);
        } elseif ('updated' === $sort) {
            $qb->orderBy('e.updatedAt', $order);
        }

        $pagerAdapter = new DoctrineORMAdapter($qb);

        return new Pagerfanta($pagerAdapter);
    }

    /**
     * Fetch an entry with a tag. Only used for tests.
     *
     * @return Entry
     */
    public function findOneWithTags($userId)
    {
        $qb = $this->createQueryBuilder('e')
            ->innerJoin('e.tags', 't')
            ->innerJoin('e.user', 'u')
            ->addSelect('t', 'u')
            ->where('e.user=:userId')->setParameter('userId', $userId)
        ;

        return $qb->getQuery()->getResult();
    }

    public function search(EntrySearch $entrySearch)
    {
        // we create a query to return all the articles
        // but if the criteria title is specified, we use it
        if ($entrySearch->getTitle() != null && $entrySearch != '') {
            $query = new \Elastica\Query\Match();
            $query->setFieldQuery('entry.title', $entrySearch->getTitle());
            $query->setFieldFuzziness('entry.title', 0.7);
            $query->setFieldMinimumShouldMatch('entry.title', '80%');
            //
        } else {
            $query = new \Elastica\Query\MatchAll();
        }
        
        // $baseQuery = $query;

        // // then we create filters depending on the chosen criterias
        // $boolFilter = new \Elastica\Filter\Bool();

        // /*
        //     Dates filter
        //     We add this filter only the getIspublished filter is not at "false"
        // */
        // if("false" != $entrySearch->getIsPublished()
        //    && null !== $entrySearch->getDateFrom()
        //    && null !== $entrySearch->getDateTo())
        // {
        //     $boolFilter->addMust(new \Elastica\Filter\Range('publishedAt',
        //         array(
        //             'gte' => \Elastica\Util::convertDate($entrySearch->getDateFrom()->getTimestamp()),
        //             'lte' => \Elastica\Util::convertDate($entrySearch->getDateTo()->getTimestamp())
        //         )
        //     ));
        // }

        // // Published or not filter
        // if($entrySearch->getIsPublished() !== null){
        //     $boolFilter->addMust(
        //         new \Elastica\Filter\Terms('published', array($entrySearch->getIsPublished()))
        //     );
        // }

        // $filtered = new \Elastica\Query\Filtered($baseQuery, $boolFilter);

        // $query = \Elastica\Query::create($filtered);

        return $this->find($query);
    }
}
