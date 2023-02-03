<?php

namespace App\Repository;

use App\Entity\Address;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Address>
 *
 * @method Address|null find($id, $lockMode = null, $lockVersion = null)
 * @method Address|null findOneBy(array $criteria, array $orderBy = null)
 * @method Address[]    findAll()
 * @method Address[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AddressRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Address::class);
    }

    public function save(Address $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Address $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    //    /**
    //     * @return Address[] Returns an array of Address objects
    //     */
    public function findByUserId($id): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.user_id = :val')
            ->setParameter('val', $id)
            ->getQuery()
            ->getResult();
    }

    public function delete($id) 
    {

       return  $this->createQueryBuilder('a')
            ->delete(Address::class, 'a')
            ->Where('a.id = :val')
            ->setParameter('val', $id)
            ->getQuery()
            ->execute();
    }
}
