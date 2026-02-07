<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;
    private string $adminPassword;
    private string $adminLogin;

    public function __construct(UserPasswordHasherInterface $passwordHasher, string $adminPassword, string $adminLogin)
    {
        $this->passwordHasher = $passwordHasher;
        $this->adminPassword = $adminPassword;
        $this->adminLogin = $adminLogin;
    }

    public function load(ObjectManager $manager): void
    {
        $user = new User();
        $user->setUsername($this->adminLogin);

        $hashedPassword = $this->passwordHasher->hashPassword(
            $user,
            $this->adminPassword
        );
        $user->setPassword($hashedPassword);
        $user->setRoles(['ROLE_ADMIN']);

        $manager->persist($user);
        $manager->flush();
    }
}
