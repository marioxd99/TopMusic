<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class SongFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $user =  new User();
        $user->setUsername('marioxd99');
        $user->setPassword('password123');

        $manager->persist($user);

        $manager->flush();
    }
}
