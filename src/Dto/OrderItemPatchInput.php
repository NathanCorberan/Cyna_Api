<?php

namespace App\Dto;

use Symfony\Component\Serializer\Annotation\Groups;
use ApiPlatform\Metadata\ApiProperty;

final class OrderItemPatchInput
{
    #[ApiProperty(example: 3)]
    #[Groups(['OrderItem:write'])]
    public ?int $quantity = null;
}
