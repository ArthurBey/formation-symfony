<?php

namespace App\DataFixtures;

use App\Entity\Ad;
use Faker\Factory;
use App\Entity\User;
use App\Entity\Image;
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
        
        //Les utilisateurs
        $users = [];
        $genres = ['male', 'female'];
        for($i = 1; $i <= 10; $i++) {
            $user = new User();

            $genre = $faker->randomElement($genres);

            $picture = "https://randomuser.me/api/portraits/";
            $pictureId = $faker->numberBetween(1, 99) . ".jpg";

            $picture .= ($genre == 'male' ? 'men/' : 'women/') . $pictureId;

            $hash = $this->encoder->encodePassword($user, 'password');

            $user->setFirstName($faker->firstname($genre)) // Faker peut génerer nom en f. du genre
                 ->setLastName($faker->lastname)
                 ->setEmail($faker->email)
                 ->setIntroduction($faker->sentence())
                 ->setDescription('<p>' . join('</p><p>', $faker->paragraphs(3)) . '</p>')
                 ->setHash($hash)
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

            $manager->persist($ad);
        }
        $manager->flush(); 
    }
}
