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

    public function createPriceForProduct(Product $product, string $price, string $label = 'Abonnement'): string
    {
        $productName = $product->getNameForLocale('fr') ?? 'Produit';

        $stripeProduct = StripeProduct::create([
            'name' => "$productName - $label",
        ]);

        $stripePrice = StripePrice::create([
            'unit_amount' => (int)((float)$price * 100),
            'currency' => 'eur',
            'recurring' => ['interval' => 'month'],
            'product' => $stripeProduct->id,
        ]);

        return $stripePrice->id;
    }
}
