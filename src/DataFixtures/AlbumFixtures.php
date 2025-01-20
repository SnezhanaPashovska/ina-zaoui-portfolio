<?php

namespace App\DataFixtures;

use App\Entity\Album;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AlbumFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $album1 = new Album();
        $album1->setName('Nature');
        $manager->persist($album1);

        $album2 = new Album();
        $album2->setName('Villes');
        $manager->persist($album2);

        $album3 = new Album();
        $album3->setName('Nourriture');
        $manager->persist($album3);

        $album4 = new Album();
        $album4->setName('Animaux');
        $manager->persist($album4);

        $album5 = new Album();
        $album5->setName('Personnes');
        $manager->persist($album5);

        $album6 = new Album();
        $album6->setName('Divers');
        $manager->persist($album6);

        $manager->flush();
    }
}
