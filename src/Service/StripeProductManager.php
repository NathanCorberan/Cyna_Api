<?php

namespace App\Service;

use Stripe\Stripe;
use Stripe\Product as StripeProduct;
use Stripe\Price as StripePrice;
use App\Entity\Product;

class StripeProductManager
{
    public function __construct()
    {
        Stripe::setApiKey($_ENV['STRIPE_SECRET_KEY']);
    }

    // Création d’un produit Stripe
    public function createStripeProduct(Product $product, string $name, ?string $description = null): string
    {
        $params = [
            'name' => $name,
        ];
        if ($description) {
            $params['description'] = $description;
        }
        $stripeProduct = StripeProduct::create($params);
        return $stripeProduct->id;
    }

    // Création d’un prix Stripe relié à un produit Stripe existant
    public function createPriceForProduct(string $stripeProductId, string $price, string $type = 'month'): string
    {
        $interval = match(strtolower($type)) {
            'mensuel', 'mois', 'month' => 'month',
            'annuel', 'an', 'year' => 'year',
            'vie', 'lifetime', 'a_vie' => null, // null pour paiement unique
            default => 'month'
        };

        $params = [
            'unit_amount' => (int)((float)$price * 100),
            'currency' => 'eur',
            'product' => $stripeProductId,
        ];
        if ($interval) {
            $params['recurring'] = ['interval' => $interval];
        }

        $stripePrice = StripePrice::create($params);

        return $stripePrice->id;
    }

    public function archivePrice(string $stripePriceId): void
    {
        StripePrice::update($stripePriceId, ['active' => false]);
    }

    public function deleteStripeProduct(string $stripeProductId): void
    {
        \Stripe\Product::update($stripeProductId, ['active' => false]);
        \Stripe\Product::delete($stripeProductId);
    }
}
