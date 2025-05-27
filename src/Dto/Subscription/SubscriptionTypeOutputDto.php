<?php

namespace App\Dto\Subscription;

class SubscriptionTypeOutputDto
{
    public int $id;
    public string $type;
    public string $price;

    public function __construct(
        int $id,
        string $type,
        string $price
    ) {
        $this->id = $id;
        $this->type = $type;
        $this->price = $price;
    }
}
