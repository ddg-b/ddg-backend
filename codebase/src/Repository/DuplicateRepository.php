<?php

namespace App\Repository;

use App\Entity\Duplicate;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Duplicate>
 *
 * @method Duplicate|null find($id, $lockMode = null, $lockVersion = null)
 * @method Duplicate|null findOneBy(array $criteria, array $orderBy = null)
 * @method Duplicate[]    findAll()
 * @method Duplicate[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DuplicateRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Duplicate::class);
    }

    public function getAllSrc(): array
    {
        $queryBuilder = $this->createQueryBuilder('duplicate');
        $queryBuilder
            ->select('duplicate.src');

        return $queryBuilder->getQuery()->getSingleColumnResult();
    }
}
