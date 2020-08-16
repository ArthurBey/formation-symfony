<?php

namespace App\Repository;

use App\Entity\Ad;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Ad|null find($id, $lockMode = null, $lockVersion = null)
 * @method Ad|null findOneBy(array $criteria, array $orderBy = null)
 * @method Ad[]    findAll()
 * @method Ad[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AdRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Ad::class);
    }

    // exploité par le HomeController pour afficher les apparts stars
    // Ancienne version : il suffit d'avoir un seul bon avis pour etre le meilleur... Cf version d'en bas
    public function findBestAds($limit) {
        return $this->createQueryBuilder('a') // On précise l'alias 'a' -> forcément celui de l'entité Ad (ici AdRepository !)
                    ->select('a as annonce, AVG(c.rating) as avgRating') // On veut récup annonces / ratings : dump pour visualiser
                    ->join('a.comments', 'c')
                    ->groupBy('a')
                    ->orderBy('avgRating', 'DESC')
                    ->setMaxResults($limit)
                    ->getQuery() // En + par rapport à la méthode queryBuilder(QUERY) 
                    ->getResult(); 
                    // [ 0 => [ "annonce" => Entité Ad, "avgRating" => note(int) ], 1 => ... ]
    }

    // /**
    //  * @return Ad[] Returns an array of Ad objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Ad
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
