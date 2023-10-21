<?php

namespace App\Repository;

use App\Entity\LogSearch;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<LogSearch>
 *
 * @method LogSearch|null find($id, $lockMode = null, $lockVersion = null)
 * @method LogSearch|null findOneBy(array $criteria, array $orderBy = null)
 * @method LogSearch[]    findAll()
 * @method LogSearch[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LogSearchRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LogSearch::class);
    }
}
