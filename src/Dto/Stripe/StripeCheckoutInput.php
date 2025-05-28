<?php

namespace App\Dto\Stripe;

use Symfony\Component\Serializer\Annotation\Groups;

class StripeCheckoutInput
{
    #[Groups(['write'])]
    public ?int $orderId = null;
}
