<?php

namespace App\Application\State\Auth;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Gesdinet\JWTRefreshTokenBundle\Model\RefreshTokenManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use App\Dto\Auth\TokenRefreshOutput;

class RefreshTokenStateProcessor implements ProcessorInterface
{
    public function __construct(
        private RefreshTokenManagerInterface $refreshTokenManager,
        private JWTTokenManagerInterface $jwtManager,
        private UserProviderInterface $userProvider,
        private RequestStack $requestStack
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): TokenRefreshOutput
    {
        // Si tu passes l'input DTO, utilise $data:
        $refreshTokenValue = $data->refresh_token ?? null;
        if (!$refreshTokenValue) {
            throw new BadRequestHttpException('Le champ "refresh_token" est requis.');
        }

        $oldToken = $this->refreshTokenManager->get($refreshTokenValue);

        if (!$oldToken) {
            throw new BadRequestHttpException('Refresh token invalide ou expiré.');
        }

        $username = $oldToken->getUsername();
        $user = $this->userProvider->loadUserByIdentifier($username);

        if (!$user instanceof UserInterface) {
            throw new BadRequestHttpException('Utilisateur introuvable.');
        }

        // Supprimer l’ancien token
        $this->refreshTokenManager->delete($oldToken);

        // Créer un nouveau refresh token
        $newRefreshToken = $this->refreshTokenManager->create();
        $newRefreshToken->setRefreshToken(); // génère une valeur random
        $newRefreshToken->setUsername($username);
        $newRefreshToken->setValid(new \DateTime('+30 days'));
        $this->refreshTokenManager->save($newRefreshToken);

        // Créer un nouveau JWT
        $jwt = $this->jwtManager->create($user);

        return new TokenRefreshOutput(
            $jwt,
            $newRefreshToken->getRefreshToken()
        );
    }
}
