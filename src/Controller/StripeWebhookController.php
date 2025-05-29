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

        // Paiement classique Stripe (paiement one-time)
        if ($event->type === 'payment_intent.succeeded') {
            $intent = $event->data->object;

            $orderId = $intent->metadata->order_id ?? null;

            if (!$orderId) {
                return new Response('Missing data', 400);
            }

            $order = $orderRepository->find($orderId);

            if ($order) {
                $order->setStatus('payed');
                $em->flush();

                $user = $order->getUser();
                if ($user) {
                    foreach ($order->getOrderItems() as $item) {
                        $subscriptionType = $item->getSubscriptionType();
                        $quantity = $item->getQuantity();

                        if (!$subscriptionType) {
                            // Si l’OrderItem ne référence pas directement, tente de prendre le premier du produit
                            $subscriptionType = $item->getProduct()->getSubscriptionTypes()->first() ?: null;
                        }

                        if (!$subscriptionType) {
                            continue; // skip
                        }

                        // Calcule les dates en fonction du type
                        $startDate = new \DateTime();
                        $type = strtolower($subscriptionType->getType() ?? 'monthly');
                        $endDate = (clone $startDate)->modify(
                            $type === 'monthly' ? '+1 month' : ($type === 'yearly' ? '+1 year' : '+1 month')
                        );

                        for ($i = 0; $i < $quantity; $i++) {
                            $subscription = new Subscription();
                            $subscription->setUser($user);
                            $subscription->setSubscriptionType($subscriptionType);
                            $subscription->setStartDate($startDate);
                            $subscription->setEndDate($endDate);
                            $subscription->setStatus('available'); // <-- statut demandé
                            $em->persist($subscription);
                        }
                    }
                    $em->flush();
                }
            }
        }

        // Paiement récurrent Stripe (abonnement via invoice)
        elseif ($event->type === 'invoice.paid') {
            $invoice = $event->data->object;

            $orderId = $invoice->lines->data[0]->metadata->order_id ?? $invoice->metadata->order_id ?? null;

            if (!$orderId) {
                return new Response('Missing data', 400);
            }

            $order = $orderRepository->find($orderId);

            if ($order) {
                $order->setStatus('payed');
                $em->flush();

                $user = $order->getUser();
                if ($user) {
                    foreach ($order->getOrderItems() as $item) {
                        $subscriptionType = $item->getSubscriptionType();
                        $quantity = $item->getQuantity();

                        if (!$subscriptionType) {
                            $subscriptionType = $item->getProduct()->getSubscriptionTypes()->first() ?: null;
                        }
                        if (!$subscriptionType) {
                            continue;
                        }

                        $startDate = new \DateTime();
                        $type = strtolower($subscriptionType->getType() ?? 'monthly');
                        $endDate = (clone $startDate)->modify(
                            $type === 'monthly' ? '+1 month' : ($type === 'yearly' ? '+1 year' : '+1 month')
                        );

                        for ($i = 0; $i < $quantity; $i++) {
                            $subscription = new Subscription();
                            $subscription->setUser($user);
                            $subscription->setSubscriptionType($subscriptionType);
                            $subscription->setStartDate($startDate);
                            if ($subscriptionType == "lifetime"){
                                $subscription->setEndDate(null);
                            }
                            else {
                                $subscription->setEndDate($endDate);
                            }
                            $subscription->setStatus('available'); // <-- statut demandé
                            $em->persist($subscription);
                        }
                    }
                    $em->flush();
                }
            }
        }

        return new Response('Webhook handled', 200);
    }
}
