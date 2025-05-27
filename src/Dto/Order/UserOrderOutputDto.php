<?php

namespace App\Dto\Order;

use ApiPlatform\Metadata\ApiProperty;

final class UserOrderOutputDto
{
    #[ApiProperty]
    public int $id;

    #[ApiProperty]
    public string $orderDate;

    #[ApiProperty]
    public string $status;

    #[ApiProperty]
    public string $totalAmount;

    #[ApiProperty]
    /** @var UserOrderProductDto[] */
    public array $products = [];

    #[ApiProperty]
    public ?string $trackingNumber = null; // si tu veux ajouter le suivi colis
}

final class UserOrderProductDto
{
    #[ApiProperty]
    public int $id;

    #[ApiProperty]
    public string $name;

    #[ApiProperty]
    public int $quantity;

    #[ApiProperty]
    public string $totalPrice;
}
