<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gesdinet\JWTRefreshTokenBundle\Entity\RefreshToken as BaseRefreshToken;
use ApiPlatform\Metadata\ApiResource;
use App\Application\State\Auth\RefreshTokenStateProcessor;
use App\Dto\Auth\RefreshTokenInput;
use ApiPlatform\Metadata\Post;

#[ORM\Entity]
#[ORM\Table(name: 'refresh_tokens')]
#[ApiResource(
    operations: [
        new Post(
            uriTemplate: '/refresh',
            input: RefreshTokenInput::class,
            output: \App\Dto\Auth\TokenRefreshOutput::class,
            processor: RefreshTokenStateProcessor::class,
            security: "is_granted('PUBLIC_ACCESS')"
        )
    ]
)]
class RefreshToken extends BaseRefreshToken
{
}
