<?php

namespace App\Application\State\Stripe;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\Stripe\StripeCheckoutInput;
use App\Dto\Stripe\StripeCheckoutOutput;
use App\Repository\OrderRepository;
use Stripe\Stripe;
use Stripe\Checkout\Session as StripeSession;

class StripeCheckoutProcessor implements ProcessorInterface
{
    public function __construct(
        private OrderRepository $orderRepository
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): StripeCheckoutOutput
    {
        /** @var StripeCheckoutInput $data */
        $order = $this->orderRepository->find($data->orderId);

        if (!$order || $order->getStatus() !== 'cart') {
            throw new \RuntimeException('Order not found or already paid');
        }

        // Préparer les items Stripe
        $lineItems = [];
        foreach ($order->getOrderItems() as $item) {
            $lineItems[] = [
                'price_data' => [
                    'currency' => 'eur',
                    'product_data' => [
                        'name' => $item->getProduct()->getProductLangage()[0]->getName(), // adapter si besoin
                    ],
                    'unit_amount' => $item->getUnitPrice() * 100,
                ],
                'quantity' => $item->getQuantity(),
            ];
        }

        // Stripe : clé secrète dans .env
        Stripe::setApiKey($_ENV['STRIPE_SECRET_KEY']);

        $session = StripeSession::create([
            'payment_method_types' => ['card'],
            'line_items' => $lineItems,
            'mode' => 'payment',
            'success_url' => $_ENV['STRIPE_SUCCESS_URL'] ?? 'https://ton-site.fr/payment-success',
            'cancel_url' => $_ENV['STRIPE_CANCEL_URL'] ?? 'https://ton-site.fr/payment-cancel',
            'metadata' => [
                'order_id' => $order->getId(),
            ],
        ]);

        return new StripeCheckoutOutput($session->url);
    }
}
