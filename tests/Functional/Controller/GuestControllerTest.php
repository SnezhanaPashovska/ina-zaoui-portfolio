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
    private $client = null;

    public function setUp(): void
    {
        parent::setUp();
        $this->client = static::createClient();

        $this->clearUsers();
        $this->loadFixtures(
            $this->getContainer()->get('security.password_hasher'),
            ['admin', 'guest']
        );
    }
    
    private function clearUsers(): void
    {
        $entityManager = $this->getContainer()->get('doctrine.orm.entity_manager');
        $users = $entityManager->getRepository(User::class)->findAll();

        foreach ($users as $user) {
            $entityManager->remove($user);
        }
        $entityManager->flush();
    }

    private function loadFixtures(UserPasswordHasherInterface $passwordHasher, array $tags): void
    {
        $manager = self::getContainer()->get('doctrine')->getManager();

        $fixture = new \App\DataFixtures\AppFixturesTest($passwordHasher, $tags);

        $fixture->load($manager);
        $manager->flush();
    }

    public function testGuestList(): void
    {
        $this->clearUsers();
        $this->loadFixtures(
            $this->getContainer()->get('security.password_hasher'),
            ['admin', 'guest']
        );

        $crawler = $this->client->request('GET', '/login');
        $this->client->submitForm('Connexion', [
            '_username' => 'guestUser',
            '_password' => 'guestpassword'
        ]);

        self::assertResponseRedirects('/');
        $this->client->followRedirect();

        $crawler = $this->client->request('GET', '/admin/guest');
        self::assertResponseIsSuccessful();
        self::assertGreaterThan(0, $crawler->filter('.guest')->count());
    }

    public function testAddGuest(): void
    {
        $adminUsername = 'ina_zaoui';
        $admin = $this->getContainer()->get('doctrine.orm.entity_manager')
            ->getRepository(User::class)
            ->findOneBy(['username' => $adminUsername]);

        $this->client->loginUser($admin);

        if (!$admin) {
            throw new \Exception("Admin user '{$adminUsername}' not found.");
        }

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
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Admin');

        $guest = $this->getContainer()->get('doctrine.orm.entity_manager')
            ->getRepository(User::class)
            ->findOneBy(['email' => $uniqueEmail]);

        self::assertNotNull($guest);
    }

    public function testGuestAccessToggle(): void
    {
        $adminUsername = 'ina_zaoui';
        $admin = $this->getContainer()->get('doctrine.orm.entity_manager')
            ->getRepository(User::class)
            ->findOneBy(['username' => $adminUsername]);

        $this->client->loginUser($admin);

        $guestUsername = 'guestUser';
        $guest = $this->getContainer()->get('doctrine.orm.entity_manager')
            ->getRepository(User::class)
            ->findOneBy(['username' => $guestUsername]);

        self::assertNotNull($guest, 'Guest user not found in the database.');
        self::assertTrue($guest->isActive(), 'Guest should initially be active.');

        $this->client->request('POST', "/admin/guest/{$guest->getId()}/toggle");
        self::assertResponseStatusCodeSame(302);

        $guest = $this->getContainer()->get('doctrine.orm.entity_manager')
            ->getRepository(User::class)
            ->find($guest->getId());
        self::assertFalse($guest->isActive(), 'Guest should now be inactive.');
    }

    public function testDeleteGuest(): void
    {
        $adminUsername = 'ina_zaoui';
        $admin = $this->getContainer()->get('doctrine.orm.entity_manager')
            ->getRepository(User::class)
            ->findOneBy(['username' => $adminUsername]);
        $this->client->loginUser($admin);

        $guestUsername = 'guestUser';
        $guest = $this->getContainer()->get('doctrine.orm.entity_manager')
            ->getRepository(User::class)
            ->findOneBy(['username' => $guestUsername]);
        self::assertNotNull($guest, 'Guest user not found in the database.');

        $guestId = $guest->getId();

        $this->client->request('POST', '/admin/guest/delete/' . $guestId);
        self::assertResponseRedirects('/admin/guest');

        $this->getContainer()->get('doctrine.orm.entity_manager')->clear();

        $deletedGuest = $this->getContainer()->get('doctrine.orm.entity_manager')
            ->getRepository(User::class)
            ->find($guestId);
        self::assertNull($deletedGuest, 'Guest user was not deleted.');
    }
}
