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
use App\Service\MailerService;

class StripeWebhookController extends AbstractController
{
    #[Route('/api/payment/webhook', name: 'stripe_webhook', methods: ['POST'])]
    public function __invoke(
        Request $request,
        UserRepository $userRepository,
        OrderRepository $orderRepository,
        EntityManagerInterface $em,
        MailerService $mailerService
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

            // **IDEMPOTENCE** : on ne traite que si pas déjà payée
            if (!$order || $order->getStatus() === 'payed') {
                return new Response('Order already processed', 200);
            }

            $user = $order->getUser();
            if ($user) {
                foreach ($order->getOrderItems() as $item) {
                    $subscriptionType = $item->getSubscriptionType();
                    $quantity = $item->getQuantity();

                    if (!$subscriptionType) {
                        $subscriptionType = $item->getProduct()->getSubscriptionTypes()->first() ?: null;
                    }

                    if (!$subscriptionType) {
                        continue; // skip
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
                        $subscription->setStartDate($startDate->format('Y-m-d'));
                        if ($type === 'lifetime') {
                            $subscription->setEndDate(null);
                        } else {
                            $subscription->setEndDate($endDate->format('Y-m-d'));
                        }
                        $subscription->setStatus('available');
                        $em->persist($subscription);
                    }
                }
            }
            // On met la commande à payée **qu'après** avoir inséré les subscriptions
            $order->setStatus('payed');
            $em->flush();
            $invoiceData = [
                'invoice_id' => $intent->id,
                'date' => (new \DateTime())->format('d/m/Y'),
                'customer_email' => $user->getEmail(),
                'items' => [],
                'total_amount' => number_format($intent->amount / 100, 2),
            ];

            foreach ($order->getOrderItems() as $item) {
                $unitPrice = $item->getPrice(); // adapte selon ton entité
                $qty = $item->getQuantity();
                $invoiceData['items'][] = [
                    'description' => $item->getProduct()->getName(), // adapte selon tes entités
                    'quantity' => $qty,
                    'unit_price' => number_format($unitPrice, 2),
                    'total_price' => number_format($unitPrice * $qty, 2),
                ];
            }

            // Envoi du mail avec la facture HTML
            $mailerService->sendInvoiceEmail($user->getEmail(), $invoiceData);

        }

        // Paiement récurrent Stripe (abonnement via invoice)
        elseif ($event->type === 'invoice.paid') {
            $invoice = $event->data->object;

            $orderId = $invoice->lines->data[0]->metadata->order_id ?? $invoice->metadata->order_id ?? null;

            if (!$orderId) {
                return new Response('Missing data', 400);
            }

            $order = $orderRepository->find($orderId);

            // **IDEMPOTENCE** : on ne traite que si pas déjà payée
            if (!$order || $order->getStatus() === 'payed') {
                return new Response('Order already processed', 200);
            }

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
                        $subscription->setStartDate($startDate->format('Y-m-d'));
                        if ($type === 'lifetime') {
                            $subscription->setEndDate(null);
                        } else {
                            $subscription->setEndDate($endDate->format('Y-m-d'));
                        }
                        $subscription->setStatus('available');
                        $em->persist($subscription);
                    }
                }
            }
            $order->setStatus('payed');
            $em->flush();
            $invoiceData = [
                'invoice_id' => $intent->id,
                'date' => (new \DateTime())->format('d/m/Y'),
                'customer_email' => $user->getEmail(),
                'items' => [],
                'total_amount' => number_format($intent->amount / 100, 2),
            ];

            foreach ($order->getOrderItems() as $item) {
                $unitPrice = $item->getPrice();
                $qty = $item->getQuantity();
                $invoiceData['items'][] = [
                    'description' => $item->getProduct()->getName(),
                    'quantity' => $qty,
                    'unit_price' => number_format($unitPrice, 2),
                    'total_price' => number_format($unitPrice * $qty, 2),
                ];
            }

            // Envoi du mail avec la facture HTML
            $mailerService->sendInvoiceEmail($user->getEmail(), $invoiceData);
        }

        return new Response('Webhook handled', 200);
    }
}
