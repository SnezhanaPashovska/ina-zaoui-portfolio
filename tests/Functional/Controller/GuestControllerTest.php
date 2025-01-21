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
    private function createUser(string $username, string $email, string $password, array $roles = ['ROLE_USER']): User
    {
        $user = new User();
        $user->setUsername($username)
            ->setEmail($email)
            ->setRoles($roles)
            ->setIsActive(true);

        $passwordHasher = $this->getContainer()->get(UserPasswordHasherInterface::class);
        $user->setPassword($passwordHasher->hashPassword($user, $password));

        $this->getContainer()->get('doctrine.orm.entity_manager')->persist($user);
        $this->getContainer()->get('doctrine.orm.entity_manager')->flush();

        return $user;
    }

    public function setUp(): void
    {
        parent::setUp();
        $this->client = static::createClient();
    }
    
    public function testGuestList(): void
    {

        $this->clearUsers();

        $guest = $this->createUser('NewGuest', 'newguest@guest.com', 'newpassword');

        $this->client->request('GET', '/login');
        $this->client->submitForm('Connexion', ['_username' => 'NewGuest', '_password' => 'newpassword']);
        $crawler = $this->client->request('GET', '/admin/guest');

        self::assertResponseIsSuccessful();
        self::assertGreaterThan(0, $crawler->filter('.guest')->count());
    }

    public function testAddGuest(): void
    {
        $adminUser = $this->createUser('adminTest', 'admin@example.com', 'password', ['ROLE_ADMIN']);

        $this->client->loginUser($adminUser);

        $uniqueUsername = 'NewGuest' . uniqid();
        $uniqueEmail = 'newguest1@guest.com' . uniqid();

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
        $adminUser = $this->createUser('adminTest' . uniqid(), 'admin-toggle' . uniqid() . '@example.com', 'password', ['ROLE_ADMIN'], true);
        $this->client->loginUser($adminUser);

        $guest = $this->createUser('GuestToggle' . uniqid(), 'guest-toggle' . uniqid() . '@example.com', 'password');
        self::assertTrue($guest->isActive());

        $this->client->request('POST', "/admin/guest/{$guest->getId()}/toggle");
        self::assertResponseStatusCodeSame(302);

        $guest = $this->getContainer()->get('doctrine.orm.entity_manager')
            ->getRepository(User::class)
            ->find($guest->getId());
        self::assertFalse($guest->isActive());
    }

    public function testDeleteGuest(): void
    {
        $adminUser = $this->createUser('adminTest' . uniqid(), 'admin-delete' . uniqid() . '@example.com', 'password', ['ROLE_ADMIN'], true);
        $this->client->loginUser($adminUser);

        $guest = $this->createUser('GuestDelete' . uniqid(), 'guest-delete' . uniqid() . '@example.com', 'password');
        $userId = $guest->getId();

        $this->client->request('POST', '/admin/guest/delete/' . $userId);
        self::assertResponseRedirects('/admin/guest');
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
}
