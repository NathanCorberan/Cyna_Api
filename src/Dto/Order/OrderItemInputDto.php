<?php

namespace App\Dto\Order;

use Symfony\Component\Serializer\Annotation\Groups;
use ApiPlatform\Metadata\ApiProperty;

final class OrderItemInputDto
{
    #[ApiProperty(example: 1)]
    #[Groups(['OrderItem:write'])]
    public ?int $product_id = null;

    #[ApiProperty(example: 2)]
    #[Groups(['OrderItem:write'])]
    public ?int $quantity = null;
}
