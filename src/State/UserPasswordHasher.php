<?php
namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;

class UserPasswordHasher implements ProcessorInterface
{
    private UserPasswordHasherInterface $passwordHasher;
    private EntityManagerInterface $entityManager;

    public function __construct(UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager)
    {
        $this->passwordHasher = $passwordHasher;
        $this->entityManager = $entityManager;
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        if (!$data instanceof User) {
            return $data;
        }

        // Hasher le mot de passe uniquement si l'utilisateur en a fourni un nouveau
        if ($data->getPassword()) {
            $hashedPassword = $this->passwordHasher->hashPassword($data, $data->getPassword());
            $data->setPassword($hashedPassword);
        }

        // Sauvegarder en base de données
        $this->entityManager->persist($data);
        $this->entityManager->flush();

        return $data;
    }
}
