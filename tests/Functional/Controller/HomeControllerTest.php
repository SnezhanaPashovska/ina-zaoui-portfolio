<?php

namespace App\Tests\Controller\Functional;

use App\DataFixtures\AppFixturesTest;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class HomeControllerTest extends WebTestCase
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

    public function testHomePageLoadsSuccessfully(): void
    {
        $this->client->request('GET', '/');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('nav');
    }

    public function testGuestsPageLoadsSuccessfully(): void
    {
        $this->client->request('GET', '/guests');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h3', 'Invités');
    }

    public function testGuestPageWithValidId(): void
    {
        $entityManager = self::getContainer()->get('doctrine')->getManager();
        $user = $entityManager->getRepository(User::class)->findOneBy(['email' => 'guest@example.com']);

        if (!$user) {
            $this->fail('User with email guest@example.com not found.');
        }

        $this->client->request('GET', '/guest/' . $user->getId());
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('h3', $user->getName());
    }

    public function testPortfolioPageLoadsSuccessfully(): void
    {
        $this->client->request('GET', '/portfolio');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h3', 'Portfolio');
    }

    public function testAboutPageLoadsSuccessfully(): void
    {
        $this->client->request('GET', '/about');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h2', 'Qui suis-je ?');
    }

    public function testAdminPageLoadsSuccessfully(): void
    {
        $entityManager = self::getContainer()->get('doctrine')->getManager();
        $adminUser = $entityManager->getRepository(User::class)->findOneBy(['email' => 'ina@zaoui.com']);

        $this->client->loginUser($adminUser);

        $this->client->request('GET', '/admin');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Admin');
    }
}
