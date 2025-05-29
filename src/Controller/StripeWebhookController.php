<?php

namespace App\Controller;

use App\Repository\UserRepository;
use App\Repository\OrderRepository;
use App\Repository\SubscriptionTypeRepository;
use App\Entity\Subscription;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
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
        SubscriptionTypeRepository $subscriptionTypeRepository,
        EntityManagerInterface $em // <-- ICI
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

        // --- Pour un paiement unique (one_time)
        if ($event->type === 'payment_intent.succeeded') {
            $intent = $event->data->object;

            $customerId = $intent->customer ?? null;
            $orderId = $intent->metadata->order_id ?? null;

            if (!$customerId || !$orderId) {
                return new Response('Missing data', 400);
            }

            $user = $userRepository->findOneBy(['stripeCustomerId' => $customerId]);
            $order = $orderRepository->find($orderId);

            if ($user && $order) {
                // Met à jour le statut de la commande
                $order->setStatus('payed');

                foreach ($order->getOrderItems() as $item) {
                    $subscriptionType = $item->getProduct()->getSubscriptionType();
                    $quantity = $item->getQuantity();
                    $startDate = (new \DateTime())->format('Y-m-d');
                    $endDate = (new \DateTime())->modify(
                        strtolower($subscriptionType->getType()) === 'monthly' ? '+1 month' : (
                            strtolower($subscriptionType->getType()) === 'yearly' ? '+1 year' : '+1 month'
                        )
                    )->format('Y-m-d');

                    for ($i = 0; $i < $quantity; $i++) {
                        $subscription = new Subscription();
                        $subscription->setUser($user);
                        $subscription->setSubscriptionType($subscriptionType);
                        $subscription->setStartDate($startDate);
                        $subscription->setEndDate($endDate);
                        $subscription->setStatus('active');
                        $em->persist($subscription);
                    }
                }

                $em->flush();
            }
        }

        // --- Pour un abonnement récurrent (monthly, yearly)
        elseif ($event->type === 'invoice.paid') {
            $invoice = $event->data->object;

            $customerId = $invoice->customer ?? null;
            $orderId = $invoice->lines->data[0]->metadata->order_id ?? $invoice->metadata->order_id ?? null;

            if (!$customerId || !$orderId) {
                return new Response('Missing data', 400);
            }

            $user = $userRepository->findOneBy(['stripeCustomerId' => $customerId]);
            $order = $orderRepository->find($orderId);

            if ($user && $order) {
                $order->setStatus('payed');

                foreach ($order->getOrderItems() as $item) {
                    $subscriptionType = $item->getProduct()->getSubscriptionType();
                    $quantity = $item->getQuantity();
                    $startDate = (new \DateTime())->format('Y-m-d');
                    $endDate = (new \DateTime())->modify(
                        strtolower($subscriptionType->getType()) === 'monthly' ? '+1 month' : (
                            strtolower($subscriptionType->getType()) === 'yearly' ? '+1 year' : '+1 month'
                        )
                    )->format('Y-m-d');

                    for ($i = 0; $i < $quantity; $i++) {
                        $subscription = new Subscription();
                        $subscription->setUser($user);
                        $subscription->setSubscriptionType($subscriptionType);
                        $subscription->setStartDate($startDate);
                        $subscription->setEndDate($endDate);
                        $subscription->setStatus('active');
                        $em->persist($subscription);
                    }
                }

                $em->flush();
            }
        }

        return new Response('Webhook handled', 200);
    }
}
