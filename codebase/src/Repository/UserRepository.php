<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<User>
 *
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly ManagerRegistry $registry,
    )
    {
        parent::__construct($registry, User::class);
    }

    function getUsersByScore(int $offset, int $itemsPerPage): array
    {
        $tableName = $this->em->getClassMetadata(User::class)->getTableName();
        $connection = $this->em->getConnection();
        $statement = $connection->prepare(
        'select
        u.display_name,
        u.username,
       u.count_gifs,
       count(distinct s.gif_id) as count_orig_gifs,
        (u.count_gifs + count(distinct s.gif_id) * 9 ) as score
        from '.$tableName.' u
        left join stats s on u.id=s.first_use_user_id
        group by u.id
        order by score desc
        LIMIT '.$itemsPerPage.'
        OFFSET '.$offset.'
        
        ');
        return $statement->executeQuery()->fetchAllAssociative();
    }
}
