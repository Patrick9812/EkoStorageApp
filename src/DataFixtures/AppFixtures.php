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
        $admin = new User();
        $admin->setUsername($this->adminLogin);
        $admin->setFullname('Główny Administrator');

        $hashedPassword = $this->passwordHasher->hashPassword(
            $admin,
            $this->adminPassword
        );
        $admin->setPassword($hashedPassword);
        $admin->setRoles(['ROLE_ADMIN']);

        $manager->persist($admin);

        $user = new User();
        $user->setUsername('pracownik1');
        $user->setFullname('Jan Kowalski');
        $user->setPassword($this->passwordHasher->hashPassword($user, 'pracownik123'));
        $user->setRoles(['ROLE_USER']);

        $manager->persist($user);

        $manager->flush();
    }
}
