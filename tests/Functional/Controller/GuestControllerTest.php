<?php

namespace App\Tests\Controller\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\DomCrawler\Form;
use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Doctrine\ORM\EntityManagerInterface;

final class GuestControllerTest extends WebTestCase
{
    private KernelBrowser $client;

    public function setUp(): void
    {
        parent::setUp();
        $this->client = static::createClient();

        $this->clearUsers();
        $this->loadFixtures(
            static::getContainer()->get('security.password_hasher'),
            ['admin', 'guest']
        );
    }

    private function clearUsers(): void
    {
        $entityManager = static::getContainer()->get('doctrine.orm.entity_manager');
        $users = $entityManager->getRepository(User::class)->findAll();

        foreach ($users as $user) {
            $entityManager->remove($user);
        }
        $entityManager->flush();
    }

    /**
     * Load the fixtures for the test.
     * 
     * @param UserPasswordHasherInterface $passwordHasher
     * @param array<string> $tags
     */
    private function loadFixtures(UserPasswordHasherInterface $passwordHasher, array $tags): void
    {
        $manager = static::getContainer()->get('doctrine')->getManager();

        $fixture = new \App\DataFixtures\AppFixturesTest($passwordHasher, $tags);
        $fixture->load($manager);

        $manager->flush();
    }

    public function testGuestList(): void
    {
        $this->clearUsers();
        $this->loadFixtures(
            static::getContainer()->get('security.password_hasher'),
            ['admin', 'guest']
        );

        $crawler = $this->client->request('GET', '/login');
        $this->client->submitForm('Connexion', [
            '_username' => 'guestUser',
            '_password' => 'guestpassword'
        ]);

        static::assertResponseRedirects('/');
        $this->client->followRedirect();

        $crawler = $this->client->request('GET', '/admin/guest');
        static::assertResponseStatusCodeSame(403);  
        
        $crawler = $this->client->request('GET', '/login');
        $this->client->submitForm('Connexion', [
            '_username' => 'ina_zaoui',
            '_password' => 'password123'
        ]);

        $this->client->followRedirect();

        $crawler = $this->client->request('GET', '/admin/guest');
        static::assertResponseIsSuccessful();
        static::assertGreaterThan(0, $crawler->filter('tr')->count());
    }

    public function testAddGuest(): void
    {
        $adminUsername = 'ina_zaoui';
        $admin = static::getContainer()->get('doctrine.orm.entity_manager')
            ->getRepository(User::class)
            ->findOneBy(['username' => $adminUsername]);

        if ($admin === null) {
            throw new \Exception("Admin user '{$adminUsername}' not found.");
        }

        $this->client->loginUser($admin);

        $uniqueUsername = 'NewGuest_' . uniqid();
        $uniqueEmail = uniqid('newguest_', true) . '@guest.com';

        $this->client->request('GET', '/admin/guest/add');
        $this->client->submitForm('Créer l\'invité', [
            'user[username]' => $uniqueUsername,
            'user[name]' => 'New Guest Name',
            'user[description]' => 'A test guest',
            'user[email]' => $uniqueEmail,
            'user[password]' => 'SecurePassword123!',
        ]);

        $this->client->followRedirect();
        static::assertResponseIsSuccessful();
        static::assertSelectorTextContains('h1', 'Admin');

        $guest = static::getContainer()->get('doctrine.orm.entity_manager')
            ->getRepository(User::class)
            ->findOneBy(['email' => $uniqueEmail]);

        static::assertNotNull($guest);
    }

    public function testGuestAccessToggle(): void
    {
        $adminUsername = 'ina_zaoui';
        $admin = static::getContainer()->get('doctrine.orm.entity_manager')
            ->getRepository(User::class)
            ->findOneBy(['username' => $adminUsername]);

        if ($admin === null) {
            throw new \Exception("Admin user '{$adminUsername}' not found.");
        }

        $this->client->loginUser($admin);

        $guestUsername = 'guestUser';
        $guest = static::getContainer()->get('doctrine.orm.entity_manager')
            ->getRepository(User::class)
            ->findOneBy(['username' => $guestUsername]);

        static::assertNotNull($guest, 'Guest user not found in the database.');
        static::assertTrue($guest->isActive(), 'Guest should initially be active.');

        $this->client->request('POST', "/admin/guest/{$guest->getId()}/toggle");
        static::assertResponseStatusCodeSame(302);

        $guest = static::getContainer()->get('doctrine.orm.entity_manager')
            ->getRepository(User::class)
            ->find($guest->getId());

        static::assertNotNull($guest, 'Guest user not found after toggling.');
        static::assertFalse($guest->isActive(), 'Guest should now be inactive.');
    }

    public function testDeleteGuest(): void
    {
        $adminUsername = 'ina_zaoui';
        $admin = static::getContainer()->get('doctrine.orm.entity_manager')
            ->getRepository(User::class)
            ->findOneBy(['username' => $adminUsername]);

        if ($admin === null) {
            throw new \Exception("Admin user '{$adminUsername}' not found.");
        }

        $this->client->loginUser($admin);

        $guestUsername = 'guestUser';
        $guest = static::getContainer()->get('doctrine.orm.entity_manager')
            ->getRepository(User::class)
            ->findOneBy(['username' => $guestUsername]);

        static::assertNotNull($guest, 'Guest user not found in the database.');

        $guestId = $guest->getId();

        $this->client->request('POST', '/admin/guest/delete/' . $guestId);
        static::assertResponseRedirects('/admin/guest');

        static::getContainer()->get('doctrine.orm.entity_manager')->clear();

        $deletedGuest = static::getContainer()->get('doctrine.orm.entity_manager')
            ->getRepository(User::class)
            ->find($guestId);
        static::assertNull($deletedGuest, 'Guest user was not deleted.');
    }
}
