<?php
namespace App\Dto\Subscription;

use Symfony\Component\Serializer\Annotation\Groups;

class MySubOutputDto
{
    #[Groups(['my_sub:read'])]
    public int $id;

    #[Groups(['my_sub:read'])]
    public string $startDate;

    #[Groups(['my_sub:read'])]
    public ?string $endDate;

    #[Groups(['my_sub:read'])]
    public string $status;

    #[Groups(['my_sub:read'])]
    public string $type;
    
    #[Groups(['my_sub:read'])]
    public float $price;

    #[Groups(['my_sub:read'])]
    public string $productTitle;

    #[Groups(['my_sub:read'])]
    public ?string $productImage;

    #[Groups(['my_sub:read'])]
    public ?string $productDescription;
}
