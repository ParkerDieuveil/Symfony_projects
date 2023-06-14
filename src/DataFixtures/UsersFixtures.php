<?php

namespace App\DataFixtures;

use App\Entity\Users;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasher;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Faker;
use Symfony\Component\String\Slugger\SluggerInterface;

class UsersFixtures extends Fixture
{

    public function __construct(private UserPasswordHasherInterface $passwordEncoder, private SluggerInterface $slugger){}
   

    public function load(ObjectManager $manager): void
    {
        $admin = new Users();
        $admin->setEmail('admin@demo.com');
        $admin->setLastname('Jules');
        $admin->setFirstName('Pierre');
        $admin->setAddress('15 rue peter pont');
        $admin->setZipcode('56001');
        $admin->setCity('Parkerland');
        $admin->setPassword(
            $this->passwordEncoder->hashPassword($admin, 'A123456N')
        );
        $admin->setRoles(['ROLE_ADMIN']);
        $manager->persist($admin);

        $faker = Faker\Factory::create('fr_FR');

        for($usr = 1; $usr <=5; $usr++){
            $user = new Users();
            $user->setEmail($faker->email);
            $user->setLastname($faker->lastName);
            $user->setFirstName($faker->firstName);
            $user->setAddress($faker->streetAddress);
            $user->setZipcode(str_replace(' ', ' ', $faker->postcode));
            $user->setCity($faker->city);
            $user->setPassword(
                $this->passwordEncoder->hashPassword($user, 'secret')
            );
        
        $manager->persist($user);
        }


        $manager->flush();
    }
}
