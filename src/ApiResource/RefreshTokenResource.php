<?php

namespace App\ApiResource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use App\Dto\Auth\RefreshTokenInput;
use App\Dto\Auth\TokenRefreshOutput;
use App\Application\State\Auth\RefreshTokenStateProcessor;

#[ApiResource(
    shortName: "Token Refresh",
    operations: [
        new Post(
            uriTemplate: '/refresh',
            input: RefreshTokenInput::class,
            output: TokenRefreshOutput::class,
            processor: RefreshTokenStateProcessor::class,
            description: "Rafraîchir un JWT à partir d'un refresh token"
        )
    ],
)]
final class RefreshTokenResource
{
    // Vide, sert juste à déclarer le endpoint.
}
