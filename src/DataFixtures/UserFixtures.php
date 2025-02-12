<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;
    

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
        
    }

    public function load(ObjectManager $manager): void
    {
        $user = new User();
        $user->setUsername('ina_zaoui');
        $user->setEmail('ina@zaoui.com');
        $user->setName('Ina Zaoui');
        $user->setAdmin(true);
        $user->setRoles(['ROLE_ADMIN']);
        $user->setDescription('Je suis l\'admin');
        $user->setIsActive(true);
        $user->setPassword(
            $this->passwordHasher->hashPassword($user, 'password123')
        );
        $manager->persist($user);

        // Guests
        $user1 = new User();
        $user1->setUsername('jean_dupont');
        $user1->setEmail('jean.dupont@example.com');
        $user1->setName('Jean Dupont');
        $user1->setAdmin(false);
        $user1->setRoles(['ROLE_USER']);
        $user1->setDescription('Utilisateur passionné par la nature.');
        $user1->setIsActive(true);
        $user1->setPassword(
            $this->passwordHasher->hashPassword($user1, 'password')
        );
        $manager->persist($user1);
        $this->addReference('user1', $user1);

        $user2 = new User();
        $user2->setUsername('marie_curie');
        $user2->setEmail('marie.curie@example.com');
        $user2->setName('Marie Curie');
        $user2->setAdmin(false);
        $user2->setRoles(['ROLE_USER']);
        $user2->setDescription('Scientifique curieuse et dévouée.');
        $user2->setIsActive(true);
        $user2->setPassword(
            $this->passwordHasher->hashPassword($user2, 'password')
        );
        $manager->persist($user2);
        $this->addReference('user2', $user2);

        $user3 = new User();
        $user3->setUsername('luc_bernard');
        $user3->setEmail('luc.bernard@example.com');
        $user3->setName('Luc Bernard');
        $user3->setAdmin(false);
        $user3->setRoles(['ROLE_USER']);
        $user3->setDescription('Voyageur enthousiaste toujours en quête de nouvelles expériences.');
        $user3->setIsActive(true);
        $user3->setPassword(
            $this->passwordHasher->hashPassword($user3, 'password')
        );
        $manager->persist($user3);
        $this->addReference('user3', $user3);

        $user4 = new User();
        $user4->setUsername('anne_legrand');
        $user4->setEmail('anne.legrand@example.com');
        $user4->setName('Anne Legrand');
        $user4->setAdmin(false);
        $user4->setRoles(['ROLE_USER']);
        $user4->setDescription('Artiste passionnée par la peinture et la sculpture.');
        $user4->setIsActive(true);
        $user4->setPassword(
            $this->passwordHasher->hashPassword($user4, 'password')
        );
        $manager->persist($user4);
        $this->addReference('user4', $user4);

        $user5 = new User();
        $user5->setUsername('pierre_durand');
        $user5->setEmail('pierre.durand@example.com');
        $user5->setName('Pierre Durand');
        $user5->setAdmin(false);
        $user5->setRoles(['ROLE_USER']);
        $user5->setDescription('Gourmand passionné par la cuisine et la pâtisserie.');
        $user5->setIsActive(true);
        $user5->setPassword(
            $this->passwordHasher->hashPassword($user5, 'password')
        );
        $manager->persist($user5);
        $this->addReference('user5', $user5);

        $manager->flush();
    }
}