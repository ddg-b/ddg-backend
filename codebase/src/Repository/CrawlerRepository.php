<?php

namespace App\Repository;

use App\Entity\Crawler;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Crawler>
 *
 * @method Crawler|null find($id, $lockMode = null, $lockVersion = null)
 * @method Crawler|null findOneBy(array $criteria, array $orderBy = null)
 * @method Crawler[]    findAll()
 * @method Crawler[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CrawlerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Crawler::class);
    }

    public function getCurrentPage(int $origin): int
    {
        return $this->findOneBy(['origin' => $origin],['page' => 'desc'])->getPage() ?? 1;
    }

    /**
     * @return Crawler[]
     */
    public function getExistingCrawledByDate(\DateTimeInterface $min_date, \DateTimeInterface $max_date, int $origin): array
    {
        $queryBuilder = $this->createQueryBuilder('crawler');
        $queryBuilder
            ->where('crawler.origin = :origin and crawler.date between :min_date and :max_date')
            ->setParameter('origin', $origin)
            ->setParameter('min_date', $min_date)
            ->setParameter('max_date', $max_date)

        ;
        return $queryBuilder->getQuery()->getResult();
    }
}
