<?php

namespace App\DataFixtures;

use App\Entity\Album;
use App\Entity\Media;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class AppFixturesTest extends Fixture
{
  private UserPasswordHasherInterface $passwordHasher;
  /**
   * @var array<string>
   */
  private array $tags;

  /**
   * @param UserPasswordHasherInterface $passwordHasher
   * @param array<string> $tags
   */

  public function __construct(UserPasswordHasherInterface $passwordHasher, array $tags)
  {
    $this->passwordHasher = $passwordHasher;
    $this->tags = $tags;
  }

  /**
   * Load the fixture data for testing.
   * 
   * @param ObjectManager $manager
   */
  public function load(ObjectManager $manager): void
  {
    // Admin user fixture
    if (count($this->tags) === 0 || in_array('admin', $this->tags, true)) {
      $admin = new User();
      $admin->setName('Ina Zaoui');
      $admin->setUsername('ina_zaoui');
      $admin->setPassword($this->passwordHasher->hashPassword($admin, 'password123'));
      $admin->setIsActive(true);
      $admin->setDescription('Test description');
      $admin->setAdmin(true);
      $admin->setEmail('ina@zaoui.com');
      $admin->setRoles(['ROLE_ADMIN']);

      $manager->persist($admin);
    }

    // Guest user fixture
    $guest = null;
    if (count($this->tags) === 0 || in_array('guest', $this->tags, true)) {
      $guest = new User();
      $guest->setUsername('guestUser');
      $guest->setName('Guest User');
      $guest->setEmail('guest@example.com');
      $guest->setDescription('Test description');
      $guest->setRoles(['ROLE_USER']);
      $guest->setIsActive(true);
      $guest->setAdmin(false);
      $guest->setPassword($this->passwordHasher->hashPassword($guest, 'guestpassword'));

      $manager->persist($guest);
    }

    // Album fixture
    $album1 = null;
    if (count($this->tags) === 0 || in_array('albums', $this->tags, true)) {
      $album1 = new Album();
      $album1->setName('Nature');
      $manager->persist($album1);
    }

    // Media fixture
    if (count($this->tags) === 0 || in_array('media', $this->tags, true)) {
      if ($guest === null || $album1 === null) {
        throw new \Exception('Guest user and album must be created before adding media.');
      }

      $media = new Media();
      $media->setUser($guest);
      $media->setAlbum($album1);
      $media->setPath('public/uploads/medias/test_media.jpg');
      $media->setTitle('Alpes');

      $testMediaPath = realpath(__DIR__ . '/Files/test_media.jpg');
      if ($testMediaPath === false) {
        throw new \Exception('Test media file not found: ' . __DIR__ . '/Files/test_media.jpg');
      }

      $media->setFile(new UploadedFile(
        $testMediaPath,
        'test_media.jpg',
        'image/jpeg',
        null,
        true
      ));
      $manager->persist($media);
    }
    $manager->flush();
  }
}
