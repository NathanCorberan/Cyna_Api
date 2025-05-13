<?php

namespace App\Dto;

use Symfony\Component\Serializer\Annotation\Groups;
use ApiPlatform\Metadata\ApiProperty;

final class OrderItemInput
{
    #[ApiProperty(example: 1)]
    #[Groups(['OrderItem:write'])]
    public ?int $product_id = null;

    #[ApiProperty(example: 2)]
    #[Groups(['OrderItem:write'])]
    public ?int $quantity = null;
}
