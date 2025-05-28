<?php

namespace App\ApiResource;

use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\ApiResource;
use App\Dto\Stripe\StripeCheckoutInput;
use App\Dto\Stripe\StripeCheckoutOutput;

#[ApiResource(
    operations: [
        new Post(
            uriTemplate: '/payment/checkout',
            input: StripeCheckoutInput::class,
            output: StripeCheckoutOutput::class,
            processor: App\Application\State\Stripe\StripeCheckoutProcessor::class
        )
    ],
    routePrefix: '/api'
)]
class StripeCheckoutResource
{
}
