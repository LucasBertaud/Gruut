<?php

namespace App\Repository;

use App\Entity\Ratings;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Ratings>
 *
 * @method Ratings|null find($id, $lockMode = null, $lockVersion = null)
 * @method Ratings|null findOneBy(array $criteria, array $orderBy = null)
 * @method Ratings[]    findAll()
 * @method Ratings[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RatingsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Ratings::class);
    }

    public function save(Ratings $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Ratings $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return Ratings[] Returns an array of Ratings objects
//     */
   public function findByProduct($id): array
   {
       return $this->createQueryBuilder('r')
           ->andWhere('r.product = :val')
           ->setParameter('val', $id)
           ->getQuery()
           ->getResult()
       ;
   }

   public function findByUserId($id): array
   {
       return $this->createQueryBuilder('r')
           ->andWhere('r.user = :val')
           ->setParameter('val', $id)
           ->getQuery()
           ->getResult()
       ;
   }

   public function delete($id) 
    {

       return  $this->createQueryBuilder('a')
            ->delete(Ratings::class, 'a')
            ->Where('a.id = :val')
            ->setParameter('val', $id)
            ->getQuery()
            ->execute();
    }
}
