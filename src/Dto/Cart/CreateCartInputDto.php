<?php

namespace App\Dto\Cart;

use Symfony\Component\Serializer\Annotation\Groups;
use ApiPlatform\Metadata\ApiProperty;

final class CreateCartInputDto
{
    #[ApiProperty(example: "cart-token-1234")]
    #[Groups(['Order:write'])]
    public ?string $key = null;

    #[ApiProperty(example: "1")]
    #[Groups(['Order:write'])]
    public ?string $user = null;
}
