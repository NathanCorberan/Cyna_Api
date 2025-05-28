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

        // Regrouper les items du panier uniquement par les types utiles
        $byType = [
            'one_time' => [],
            'monthly'  => [],
            'yearly'   => [],
        ];

        foreach ($order->getOrderItems() as $item) {
            $type = strtolower($item->getProduct()->getSubscriptionType()->getType());
            if (!isset($byType[$type])) {
                $byType['one_time'][] = $item; // Si le type n'est pas connu, on le classe en one_time par défaut
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
                $stripePriceId = $item->getProduct()->getSubscriptionType()->getStripePriceId();
                $items[] = [
                    'price'    => $stripePriceId,
                    'quantity' => $item->getQuantity()
                ];
            }
            $subscription = Subscription::create([
                'customer' => $customerId,
                'items' => $items,
                'default_payment_method' => $paymentMethodId,
                'metadata' => [
                    'order_id' => $orderId,
                    'type'     => 'monthly'
                ],
                'expand' => ['latest_invoice.payment_intent'],
            ]);
            $stripeResults['monthly'] = [
                'subscription_id' => $subscription->id,
                'client_secret'   => $subscription->latest_invoice->payment_intent->client_secret ?? null,
                'status'          => $subscription->status
            ];
        }

        // --- YEARLY SUBSCRIPTIONS ---
        if (count($byType['yearly']) > 0) {
            $items = [];
            foreach ($byType['yearly'] as $item) {
                $stripePriceId = $item->getProduct()->getSubscriptionType()->getStripePriceId();
                $items[] = [
                    'price'    => $stripePriceId,
                    'quantity' => $item->getQuantity()
                ];
            }
            $subscription = Subscription::create([
                'customer' => $customerId,
                'items' => $items,
                'default_payment_method' => $paymentMethodId,
                'metadata' => [
                    'order_id' => $orderId,
                    'type'     => 'yearly'
                ],
                'expand' => ['latest_invoice.payment_intent'],
            ]);
            $stripeResults['yearly'] = [
                'subscription_id' => $subscription->id,
                'client_secret'   => $subscription->latest_invoice->payment_intent->client_secret ?? null,
                'status'          => $subscription->status
            ];
        }

        return $this->json(['stripe_results' => $stripeResults]);
    }
}
