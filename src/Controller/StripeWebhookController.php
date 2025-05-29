<?php

namespace App\Controller;

use App\Repository\UserRepository;
use App\Repository\OrderRepository;
use App\Entity\Subscription;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Stripe\Stripe;
use Stripe\Webhook;

class StripeWebhookController extends AbstractController
{
    #[Route('/api/payment/webhook', name: 'stripe_webhook', methods: ['POST'])]
    public function __invoke(
        Request $request,
        UserRepository $userRepository,
        OrderRepository $orderRepository,
        EntityManagerInterface $em
    ) {
        Stripe::setApiKey($_ENV['STRIPE_SECRET_KEY']);
        $payload = $request->getContent();
        $sig_header = $request->headers->get('stripe-signature');
        $endpoint_secret = $_ENV['STRIPE_WEBHOOK_SECRET'];

        try {
            $event = Webhook::constructEvent($payload, $sig_header, $endpoint_secret);
        } catch (\Exception $e) {
            return new Response('Webhook Error: ' . $e->getMessage(), 400);
        }

        // === 1. Paiement unique (one_time/lifetime via payment_intent) ===
        if (isset($event->data->object->object) && $event->data->object->object === 'payment_intent') {
            $intent = $event->data->object;
            $metadata = (array) ($intent->metadata ?? []);
            $orderId = $metadata['order_id'] ?? null;

            if (!$orderId) {
                return new Response('Missing order_id', 400);
            }

            $order = $orderRepository->find($orderId);
            if (!$order) return new Response('Order not found', 404);

            $user = $order->getUser();
            if ($user) {
                foreach ($order->getOrderItems() as $item) {
                    $subscriptionType = $item->getSubscriptionType() ?: $item->getProduct()->getSubscriptionTypes()->first() ?: null;
                    if (!$subscriptionType) continue;

                    $type = strtolower($subscriptionType->getType() ?? '');
                    if ($type !== 'lifetime' && $type !== 'one_time') continue;

                    $quantity = $item->getQuantity() ?? 1;
                    for ($i = 0; $i < $quantity; $i++) {
                        $subscription = new Subscription();
                        $subscription->setUser($user);
                        $subscription->setSubscriptionType($subscriptionType);
                        $subscription->setStartDate((new \DateTime())->format('Y-m-d'));
                        $subscription->setEndDate(null);
                        $subscription->setStatus('available');
                        $em->persist($subscription);
                    }
                }
                $order->setStatus('payed');
                $em->flush();
            }
        }

        // === 2. Abonnement Stripe (subscription) ===
        elseif (isset($event->data->object->object) && $event->data->object->object === 'subscription') {
            $subscriptionStripe = $event->data->object;
            $metadata = (array) ($subscriptionStripe->metadata ?? []);
            $orderId = $metadata['order_id'] ?? null;
            $type = strtolower($metadata['type'] ?? 'monthly');

            if (!$orderId) {
                return new Response('Missing order_id', 400);
            }
            $order = $orderRepository->find($orderId);
            if (!$order) return new Response('Order not found', 404);

            $user = $order->getUser();
            if ($user) {
                foreach ($order->getOrderItems() as $item) {
                    $subscriptionType = $item->getSubscriptionType() ?: $item->getProduct()->getSubscriptionTypes()->first() ?: null;
                    if (!$subscriptionType) continue;

                    $stype = strtolower($subscriptionType->getType() ?? 'monthly');
                    if ($stype === 'lifetime' || $stype === 'one_time') continue;

                    $quantity = $item->getQuantity() ?? 1;
                    $startDate = new \DateTime();
                    $endDate = (clone $startDate)->modify(
                        $stype === 'monthly' ? '+1 month' : (
                            $stype === 'yearly' ? '+1 year' : '+1 month'
                        )
                    );

                    for ($i = 0; $i < $quantity; $i++) {
                        $subscription = new Subscription();
                        $subscription->setUser($user);
                        $subscription->setSubscriptionType($subscriptionType);
                        $subscription->setStartDate($startDate->format('Y-m-d'));
                        $subscription->setEndDate($endDate->format('Y-m-d'));
                        $subscription->setStatus('available');
                        $em->persist($subscription);
                    }
                }
                $order->setStatus('payed');
                $em->flush();
            }
        }

        return new Response('Webhook handled', 200);
    }
}
