<?php

namespace App\DataFixtures;

use App\Entity\Ad;
use Faker\Factory;
use App\Entity\Role;
use App\Entity\User;
use App\Entity\Image;
use App\Entity\Booking;
use App\Entity\Comment;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{
    private $encoder;

    // On a beson d'encoder le password (seucrity.yaml encoder bien déclaré) mais service soit via controller/constructeur
    public function __construct(UserPasswordEncoderInterface $encoder) {
        $this->encoder = $encoder;
    }

    public function load(ObjectManager $manager)
    {
        $faker = Factory::create('FR-fr');

        // On créer un Rôle 'admin' (une entité Role) puis un utilisateur admin
        $adminRole = new Role();
        $adminRole->setTitle('ROLE_ADMIN'); // Note : Pour chaque entité User on a deja getRoles qui renvoi par dégaut ['ROLE_USER']
        $manager->persist($adminRole);
        $adminUser = new User(); // On créer un utilisateur Admin
        $adminUser->setFirstName('Arthur')
                  ->setLastName('Bachirov')
                  ->setEmail('arthur.bachirov@gmail.com')
                  ->setPassword($this->encoder->encodePassword($adminUser, 'Password'))
                  ->setPicture('https://encrypted-tbn0.gstatic.com/images?q=tbn%3AANd9GcTHwT4KCr035BrAOXryRaf1bT1eF3914jKe2A&usqp=CAU')
                  ->setIntroduction($faker->sentence())
                  ->setDescription('<p>' . join('</p><p>', $faker->paragraphs(3)) . '</p>')
                  ->addUserRole($adminRole); // ICI ON AJOUTE LENTITE ROLE D'ADMIN
        $manager->persist($adminUser); 

        //Les utilisateurs
        $users = [];
        $genres = ['male', 'female'];
        for($i = 1; $i <= 10; $i++) {
            $user = new User();

            $genre     = $faker->randomElement($genres);

            $picture   = "https://randomuser.me/api/portraits/";
            $pictureId = $faker->numberBetween(1, 99) . ".jpg";

            $picture  .= ($genre == 'male' ? 'men/' : 'women/') . $pictureId;

            $hash      = $this->encoder->encodePassword($user, 'password');

            $user->setFirstName($faker->firstname($genre)) // Faker peut génerer nom en f. du genre
                 ->setLastName($faker->lastname)
                 ->setEmail($faker->email)
                 ->setIntroduction($faker->sentence())
                 ->setDescription('<p>' . join('</p><p>', $faker->paragraphs(3)) . '</p>')
                 ->setPassword($hash)
                 ->setPicture($picture);

            $manager->persist($user);
            $users[] = $user;
        }

        // Les annonces
        for($i = 1; $i <= 30; $i++) {
            $ad = new Ad();

            // On veux tirer aléatoirement un des 10 utilisateurs crée
            $user = $users[mt_rand(0, count($users) - 1)]; // -1 car 1er user -> 0...
            
            $ad->setTitle($faker->sentence(5))
                ->setCoverImage($faker->imageUrl(1000, 350))
                ->setIntroduction($faker->paragraph(2))
                ->setContent('<p>' . implode('</p><p>', $faker->paragraphs(5)) . '</p>')
                ->setPrice(mt_rand(40, 200))
                ->setRooms(mt_rand(1, 5))
                ->setAuthor($user);

            for ($j = 1; $j <= mt_rand(2, 5); $j++) {
                $image = new Image();
                $image->setUrl($faker->imageUrl())
                      ->setCaption($faker->sentence())
                      ->setAd($ad);
                $manager->persist($image);

            }

            // Gestion des résa (pour une annonce)
            for($j = 1; $j <= mt_rand(0, 10) ; $j++){
                $booking = new Booking();

                $createdAt = $faker->dateTimeBetween('- 6 months');
                $startDate = $faker->dateTimeBetween('- 3 months');
                // gestion date de départ
                $duration  = mt_rand(3, 10);
                $endDate   = (clone $startDate)->modify("+$duration days"); /* modify() est une méthode des objets DateTime()
                ‘clone’ permet de créer une copie iddentique de l’objet, car sinon ici la méthode modify va également modifier la variable $startDate ! */
                $amount    = $ad->getPrice() * $duration;
                $booker    = $users[mt_rand(0, count($users) -1)]; // -1 car 1er user index 0
                $comment   = $faker->paragraph(); // Commentaire style "Nous voudrions faire checkine plus tot euh... "
                
                $booking->setBooker($booker)
                        ->setAd($ad)
                        ->setStartDate($startDate)
                        ->setEndDate($endDate)
                        ->setCreatedAt($createdAt)
                        ->setAmount($amount)
                        ->setComment($comment);

                $manager->persist($booking);

                // On génère les comments dans la boucle des résa, car pas possible d'avoir comments avec annonces réservée 0 fois
                if(mt_rand(0, 1)) { // -> pile ou face : soit la résa aura un/des comment(s) soit elle n'en aura pas
                    $comment = new Comment();
                    $comment->setContent($faker->paragraph())
                            ->setRating(mt_rand(1, 5))
                            ->setAuthor($booker)
                            ->setAd($ad); 
                            // le createdAt déterminé dans l'entité avec un prePersist
                    $manager->persist($comment);
                }
            }

            $manager->persist($ad);
        }
        $manager->flush(); 
    }
}
