<?php

namespace App\Dto;

use Symfony\Component\Serializer\Annotation\Groups;
use ApiPlatform\Metadata\ApiProperty;

final class CreateCartInput
{
    #[ApiProperty(example: "cart-token-1234")]
    #[Groups(['Order:write'])]
    public ?string $key = null;

    #[ApiProperty(example: "1")]
    #[Groups(['Order:write'])]
    public ?string $user = null;
}
