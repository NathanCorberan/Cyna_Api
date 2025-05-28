<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Repository\OrderRepository;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use Stripe\Subscription;

class StripeCheckoutController extends AbstractController
{
    #[Route('/api/payment/checkout', name: 'stripe_checkout', methods: ['POST'])]
    public function __invoke(Request $request, OrderRepository $orderRepository)
    {
        $data = json_decode($request->getContent(), true);
        $orderId = $data['order_id'] ?? null;
        $paymentMethodId = $data['payment_method_id'] ?? null;
        $user = $this->getUser();

        if (!$orderId || !$paymentMethodId) {
            return $this->json(['error' => 'Missing data'], 400);
        }

        $order = $orderRepository->find($orderId);
        if (!$order) return $this->json(['error' => 'Order not found'], 404);

        Stripe::setApiKey($_ENV['STRIPE_SECRET_KEY']);
        $customerId = $user->getStripeCustomerId();

        $byType = [
            'one_time' => [],
            'monthly'  => [],
            'yearly'   => [],
        ];

        foreach ($order->getOrderItems() as $item) {
            $subscriptionType = $item->getSubscriptionType();
            if (!$subscriptionType) {
                $byType['one_time'][] = $item;
                continue;
            }
            $type = strtolower($subscriptionType->getType());
            if (!isset($byType[$type])) {
                $byType['one_time'][] = $item;
            } else {
                $byType[$type][] = $item;
            }
        }

        $stripeResults = [];

        // --- ONE TIME PAYMENT (PaymentIntent) ---
        if (count($byType['one_time']) > 0) {
            $amount = 0;
            foreach ($byType['one_time'] as $item) {
                $amount += $item->getUnitPrice() * $item->getQuantity();
            }
            $paymentIntent = PaymentIntent::create([
                'amount' => intval($amount * 100),
                'currency' => 'eur',
                'customer' => $customerId,
                'payment_method' => $paymentMethodId,
                'off_session' => true,
                'confirm' => true,
                'metadata' => [
                    'order_id' => $orderId,
                    'type' => 'one_time'
                ]
            ]);
            $stripeResults['one_time'] = [
                'payment_intent_id' => $paymentIntent->id,
                'client_secret' => $paymentIntent->client_secret
            ];
        }

        // --- MONTHLY SUBSCRIPTIONS ---
        if (count($byType['monthly']) > 0) {
            $items = [];
            foreach ($byType['monthly'] as $item) {
                $subscriptionType = $item->getSubscriptionType();
                if ($subscriptionType) {
                    $stripePriceId = $subscriptionType->getStripePriceId();
                    $items[] = [
                        'price'    => $stripePriceId,
                        'quantity' => $item->getQuantity()
                    ];
                }
            }
            if (!empty($items)) {
                $subscription = Subscription::create([
                    'customer' => $customerId,
                    'items' => $items,
                    'default_payment_method' => $paymentMethodId,
                    'metadata' => [
                        'order_id' => $orderId,
                        'type'     => 'monthly'
                    ],
                    'expand' => ['latest_invoice.confirmation_secret'],
                ]);
                $clientSecret = null;
                if (
                    isset($subscription->latest_invoice) &&
                    isset($subscription->latest_invoice->confirmation_secret)
                ) {
                    $clientSecret = $subscription->latest_invoice->confirmation_secret->client_secret;
                }
                $stripeResults['monthly'] = [
                    'subscription_id' => $subscription->id,
                    'client_secret'   => $clientSecret,
                    'status'          => $subscription->status
                ];
            }
        }

        // --- YEARLY SUBSCRIPTIONS ---
        if (count($byType['yearly']) > 0) {
            $items = [];
            foreach ($byType['yearly'] as $item) {
                $subscriptionType = $item->getSubscriptionType();
                if ($subscriptionType) {
                    $stripePriceId = $subscriptionType->getStripePriceId();
                    $items[] = [
                        'price'    => $stripePriceId,
                        'quantity' => $item->getQuantity()
                    ];
                }
            }
            if (!empty($items)) {
                $subscription = Subscription::create([
                    'customer' => $customerId,
                    'items' => $items,
                    'default_payment_method' => $paymentMethodId,
                    'metadata' => [
                        'order_id' => $orderId,
                        'type'     => 'yearly'
                    ],
                    'expand' => ['latest_invoice.confirmation_secret'],
                ]);
                $clientSecret = null;
                if (
                    isset($subscription->latest_invoice) &&
                    isset($subscription->latest_invoice->confirmation_secret)
                ) {
                    $clientSecret = $subscription->latest_invoice->confirmation_secret->client_secret;
                }
                $stripeResults['yearly'] = [
                    'subscription_id' => $subscription->id,
                    'client_secret'   => $clientSecret,
                    'status'          => $subscription->status
                ];
            }
        }

        return $this->json(['stripe_results' => $stripeResults]);
    }
}
