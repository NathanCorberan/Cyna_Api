<?php

namespace App\Application\State\User;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Symfony\Bundle\SecurityBundle\Security;
use App\Entity\User;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UserMeProvider implements ProviderInterface
{
    private Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): ?User
    {
        $user = $this->security->getUser();

        if (!$user instanceof User) {
            error_log('🔴 Aucun utilisateur trouvé dans UserMeProvider.');
            throw new NotFoundHttpException('User not found.');
        }

        error_log('🟢 Utilisateur trouvé : ' . $user->getEmail());

        return $user;
    }


}
    