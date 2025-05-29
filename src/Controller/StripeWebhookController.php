<?php

namespace App\Controller;

use App\Repository\UserRepository;
use App\Repository\OrderRepository;
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
            }
        }

        return new Response('Webhook handled', 200);
    }
}
