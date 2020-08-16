<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(UserInterface $user, string $newEncodedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', \get_class($user)));
        }

        $user->setPassword($newEncodedPassword);
        $this->_em->persist($user);
        $this->_em->flush();
    }

    // Dans cette ancienne version il suffisait d'avoir un seul avis 5/5 pour etre considéré comme le meilleur utilisateur
    public function findBestUsersOld($limit = 2) {
        return $this->createQueryBuilder('u') // On précise l'alias 'u' -> forcément celui de l'entité User
                    ->select('u as user, AVG(c.rating) as avgRatings')
                    ->join('u.ads', 'a')
                    ->join('a.comments', 'c')
                    ->groupBy('u')
                    ->orderBy('avgRatings', 'DESC')
                    ->setMaxResults($limit)
                    ->getQuery()
                    ->getResult();
    }

    // Dans cette nouvelle version sont uniquement pris en compte annonces avec + de 3 avis
    public function findBestUsers($limit = 2) {
        return $this->createQueryBuilder('u') // On précise l'alias 'u' -> forcément celui de l'entité User
                    ->select('u as user, AVG(c.rating) as avgRatings, COUNT(c) as sumComments') // COUNT(c) pour faire un HAVING sumComments > 3
                    ->join('u.ads', 'a')
                    ->join('a.comments', 'c')
                    ->groupBy('u') // Pour faire un having, il faut forcément avoir un groupement
                    ->having('sumComments > 3') // Ainsi on récupère meilleures annonces des ads avec + de 3 avis
                    ->orderBy('avgRatings', 'DESC')
                    ->setMaxResults($limit)
                    ->getQuery()
                    ->getResult();
    }
                    
    // /**
    //  * @return User[] Returns an array of User objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('u.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?User
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
