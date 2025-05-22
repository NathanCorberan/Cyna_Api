<?php

namespace App\Dto\Order;

use Symfony\Component\Serializer\Annotation\Groups;
use ApiPlatform\Metadata\ApiProperty;

final class OrderItemPatchInputDto
{
    #[ApiProperty(example: 3)]
    #[Groups(['OrderItem:write'])]
    public ?int $quantity = null;
}
