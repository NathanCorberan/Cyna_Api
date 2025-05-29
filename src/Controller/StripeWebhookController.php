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

        // 1. Paiement unique (one_time/lifetime)
        if ($event->type === 'payment_intent.succeeded') {
            $intent = $event->data->object;
            $metadata = (array) ($intent->metadata ?? []);
            $orderId = $metadata['order_id'] ?? null;
            $type = strtolower($metadata['type'] ?? 'one_time');

            if (!$orderId) {
                return new Response('Missing data', 400);
            }
            $order = $orderRepository->find($orderId);
            if (!$order) return new Response('Order not found', 404);
            if ($order->getStatus() === 'payed') return new Response('Order already payed', 200);

            $user = $order->getUser();
            if ($user) {
                foreach ($order->getOrderItems() as $item) {
                    $subscriptionType = $item->getSubscriptionType() ?: $item->getProduct()->getSubscriptionTypes()->first() ?: null;
                    $quantity = $item->getQuantity();

                    if (!$subscriptionType) continue;

                    $startDate = new \DateTime();
                    for ($i = 0; $i < $quantity; $i++) {
                        $subscription = new Subscription();
                        $subscription->setUser($user);
                        $subscription->setSubscriptionType($subscriptionType);
                        $subscription->setStartDate($startDate->format('Y-m-d'));
                        if ($type === 'lifetime' || $type === 'one_time') {
                            $subscription->setEndDate(null);
                        } else {
                            $endDate = (clone $startDate)->modify(
                                $type === 'monthly' ? '+1 month' :
                                ($type === 'yearly' ? '+1 year' : '+1 month')
                            );
                            $subscription->setEndDate($endDate->format('Y-m-d'));
                        }
                        $subscription->setStatus('available');
                        $em->persist($subscription);
                    }
                }
                $order->setStatus('payed');
                $em->flush();
            }
        }

        // 2. Abonnement Stripe via invoice (monthly/yearly)
        elseif ($event->type === 'invoice.paid') {
            $invoice = $event->data->object;
            // Méthode STRIPE qui change souvent : tu checkes dans plusieurs endroits
            $metadata = [];
            // 1. Stripe met le metadata sur invoice direct
            if (isset($invoice->metadata)) {
                $metadata = (array) $invoice->metadata;
            }
            // 2. ... ou sur la première line (produit)
            if (empty($metadata) && isset($invoice->lines->data[0]->metadata)) {
                $metadata = (array) $invoice->lines->data[0]->metadata;
            }
            $orderId = $metadata['order_id'] ?? null;
            $type = strtolower($metadata['type'] ?? 'monthly'); // fallback sur monthly

            if (!$orderId) {
                return new Response('Missing data', 400);
            }
            $order = $orderRepository->find($orderId);
            if (!$order) return new Response('Order not found', 404);
            if ($order->getStatus() === 'payed') return new Response('Order already payed', 200);

            $user = $order->getUser();
            if ($user) {
                foreach ($order->getOrderItems() as $item) {
                    $subscriptionType = $item->getSubscriptionType() ?: $item->getProduct()->getSubscriptionTypes()->first() ?: null;
                    $quantity = $item->getQuantity();

                    if (!$subscriptionType) continue;

                    $startDate = new \DateTime();
                    for ($i = 0; $i < $quantity; $i++) {
                        $subscription = new Subscription();
                        $subscription->setUser($user);
                        $subscription->setSubscriptionType($subscriptionType);
                        $subscription->setStartDate($startDate->format('Y-m-d'));
                        if ($type === 'lifetime' || $type === 'one_time') {
                            $subscription->setEndDate(null);
                        } else {
                            $endDate = (clone $startDate)->modify(
                                $type === 'monthly' ? '+1 month' :
                                ($type === 'yearly' ? '+1 year' : '+1 month')
                            );
                            $subscription->setEndDate($endDate->format('Y-m-d'));
                        }
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
