<?php

namespace App\Tests;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SongTest extends WebTestCase
{
    public function testSomething(): void
    {
        $userRepository = static::getContainer()->get(UserRepository::class);
        $testUser = $userRepository->findOneByUsername('marioxd99');

        $this->assertNotNull($testUser);
    }


    public function testLogin()
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);

        $testUser = $userRepository->findOneByUsername('marioxd99');

        $client->loginUser($testUser);

        $client->request('GET', '/login');
        $this->assertResponseIsSuccessful();

    }
}
