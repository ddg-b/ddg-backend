<?php

namespace App\Repository;

use App\Entity\Gif;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Gif>
 *
 * @method Gif|null find($id, $lockMode = null, $lockVersion = null)
 * @method Gif|null findOneBy(array $criteria, array $orderBy = null)
 * @method Gif[]    findAll()
 * @method Gif[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GifRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Gif::class);
    }

    public function getAllSrc(): array
    {
        $queryBuilder = $this->createQueryBuilder('gif');
        $queryBuilder
            ->select('gif.src');
        return $queryBuilder->getQuery()->getSingleColumnResult();
    }
}
