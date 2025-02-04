<?php

namespace App\Tests\Functional\Controller;

use App\Tests\AppFixturesTest;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

class HomeControllerTest extends WebTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = static::createClient();

        $this->clearUsers();

        $this->loadFixtures(
            static::getContainer()->get(UserPasswordHasherInterface::class),
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

        $fixture = new AppFixturesTest($passwordHasher, $tags);
        $fixture->load($manager);

        $manager->flush();
    }

    public function testHomePageLoadsSuccessfully(): void
    {
        $this->client->request('GET', '/');
        static::assertResponseIsSuccessful();
        static::assertSelectorExists('nav');
    }

    public function testGuestsPageLoadsSuccessfully(): void
    {
        $this->client->request('GET', '/guests');
        static::assertResponseIsSuccessful();
        static::assertSelectorTextContains('h3', 'Invités');
    }

    public function testGuestPageWithValidId(): void
    {
        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $user = $entityManager->getRepository(User::class)->findOneBy(['email' => 'guest@example.com']);

        if ($user === null) {
            static::fail('User with email guest@example.com not found.');
        }

        $this->client->request('GET', '/guest/' . $user->getId());
        static::assertResponseIsSuccessful();
        static::assertSelectorExists('h3', (string)$user->getName());
    }

    public function testPortfolioPageLoadsSuccessfully(): void
    {
        $this->client->request('GET', '/portfolio');
        static::assertResponseIsSuccessful();
        static::assertSelectorTextContains('h3', 'Portfolio');
    }

    public function testAboutPageLoadsSuccessfully(): void
    {
        $this->client->request('GET', '/about');
        static::assertResponseIsSuccessful();
        static::assertSelectorTextContains('h2', 'Qui suis-je ?');
    }

    public function testAdminPageLoadsSuccessfully(): void
    {
        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $adminUser = $entityManager->getRepository(User::class)->findOneBy(['email' => 'ina@zaoui.com']);

        if ($adminUser === null) {
            throw new \Exception('Admin user not found.');
        }

        $this->client->loginUser($adminUser);

        $this->client->request('GET', '/admin');
        static::assertResponseIsSuccessful();
        static::assertSelectorTextContains('h1', 'Admin');
    }
}
