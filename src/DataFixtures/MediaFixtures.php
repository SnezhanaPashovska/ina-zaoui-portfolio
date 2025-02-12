<?php

namespace App\DataFixtures;

use App\Entity\Media;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\User;
use App\Entity\Album;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class MediaFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {

        // User 1
        $user1 = $this->getReference('user1', User::class); 
        // Album 1
        $album1 = $this->getReference('album1', Album::class);
        

        $media1 = new Media();
        $media1->setTitle('Beach')
            ->setPath('/uploads/0006.jpg')
            ->setUser($user1)
            ->setAlbum($album1);
        $manager->persist($media1);

        // User 2
        $user2 = $this->getReference('user2', User::class);
        // Album 2
        $album2 = $this->getReference('album2', Album::class);
       

        $media2 = new Media();
        $media2->setTitle('Town river')
            ->setPath('/uploads/0043.jpg')
            ->setUser($user2)
            ->setAlbum($album2);
        $manager->persist($media2);

        // User 3
        $user3 = $this->getReference('user3', User::class);
        // Album 3
        $album3 = $this->getReference('album3', Album::class);
      

        $media3 = new Media();
        $media3->setTitle('Raspberries')
            ->setPath('/uploads/0351.jpg')
            ->setUser($user3)
            ->setAlbum($album3);
        $manager->persist($media3);

        // User 4
        $user4 = $this->getReference('user4', User::class);
        // Album 4
        $album4 = $this->getReference('album4', Album::class);
       

        $media4 = new Media();
        $media4->setTitle('Cat')
            ->setPath('/uploads/0745.jpg')
            ->setUser($user4)
            ->setAlbum($album4);
        $manager->persist($media4);

        // User 5
        $user5 = $this->getReference('user5', User::class);
        // Album 5
        $album5 = $this->getReference('album5', Album::class);
      

        $media5 = new Media();
        $media5->setTitle('Legs')
            ->setPath('/uploads/0210.jpg')
            ->setUser($user5)
            ->setAlbum($album5);
        $manager->persist($media5);

        // Album 6
        $album6 = $this->getReference('album6', Album::class);

        $media6 = new Media();
        $media6->setTitle('Gadgets')
            ->setPath('/uploads/0275.jpg')
            ->setUser($user5)
            ->setAlbum($album6);
        $manager->persist($media6);


        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
            AlbumFixtures::class,
        ];
    }
}
