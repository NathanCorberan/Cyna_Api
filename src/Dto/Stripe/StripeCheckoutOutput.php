<?php

namespace App\Dto\Stripe;

use Symfony\Component\Serializer\Annotation\Groups;

class StripeCheckoutOutput
{
    #[Groups(['read'])]
    public ?string $url = null;

    public function __construct(string $url)
    {
        $this->url = $url;
    }
}
