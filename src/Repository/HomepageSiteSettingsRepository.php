<?php

namespace App\Repository;

use App\Entity\HomepageSiteSettings;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method HomepageSiteSettings|null find($id, $lockMode = null, $lockVersion = null)
 * @method HomepageSiteSettings|null findOneBy(array $criteria, array $orderBy = null)
 * @method HomepageSiteSettings[]    findAll()
 * @method HomepageSiteSettings[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class HomepageSiteSettingsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, HomepageSiteSettings::class);
    }

    // /**
    //  * @return HomepageSiteSettings[] Returns an array of HomepageSiteSettings objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('h')
            ->andWhere('h.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('h.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?HomepageSiteSettings
    {
        return $this->createQueryBuilder('h')
            ->andWhere('h.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
