<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use App\Dto\PasswordChangeDTO;
use App\Entity\User;
use App\State\UserPasswordHasher;

class UserPasswordChangeStateProcessor implements ProcessorInterface
{
    private Security $security;
    private EntityManagerInterface $entityManager;
    private UserPasswordHasher $passwordHasher;

    public function __construct(
        Security $security, 
        EntityManagerInterface $entityManager, 
        UserPasswordHasher $passwordHasher
    ) {
        $this->security = $security;
        $this->entityManager = $entityManager;
        $this->passwordHasher = $passwordHasher;
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        // ✅ Vérifier que l'entrée est bien un DTO
        if (!$data instanceof PasswordChangeDTO) {
            throw new BadRequestException('Données invalides.');
        }

        // ✅ Récupérer l'utilisateur connecté
        $user = $this->security->getUser();
        if (!$user instanceof User) {
            throw new BadRequestException('Utilisateur non authentifié.');
        }

        // ✅ Vérifier l'ancien mot de passe
        if (!password_verify($data->last_password, $user->getPassword())) {
            throw new BadRequestException('L\'ancien mot de passe est incorrect.');
        }

        // ✅ Vérifier que le nouveau mot de passe est différent
        if ($data->last_password === $data->new_password) {
            throw new BadRequestException('Le nouveau mot de passe doit être différent de l\'ancien.');
        }

        // ✅ Vérifier si la confirmation correspond au nouveau mot de passe
        if ($data->new_password !== $data->confirmation_password) {
            throw new BadRequestException('Le nouveau mot de passe et la confirmation ne correspondent pas.');
        }

        // ✅ Mettre à jour l'utilisateur avec le nouveau mot de passe
        $user->setPassword($data->new_password);
        $this->passwordHasher->process($user, $operation, $uriVariables, $context);

        return ['success' => 'Mot de passe modifié avec succès'];
    }
}
