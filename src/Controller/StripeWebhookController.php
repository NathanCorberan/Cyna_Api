<?php

namespace App\Controller;

use App\Repository\OrderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class StripeWebhookController extends AbstractController
{
    #[Route('/api/payment/webhook', name: 'stripe_webhook', methods: ['POST'])]
    public function webhook(
        Request $request,
        OrderRepository $orderRepo,
        EntityManagerInterface $em
    ): Response {
        $payload = $request->getContent();
        $sig_header = $request->headers->get('stripe-signature');
        $endpoint_secret = $_ENV['STRIPE_WEBHOOK_SECRET'] ?? '';

        // Vérifie la signature Stripe pour la sécurité
        try {
            $event = \Stripe\Webhook::constructEvent($payload, $sig_header, $endpoint_secret);
        } catch (\UnexpectedValueException $e) {
            // Mauvais payload
            return new Response('Invalid payload', 400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            // Mauvaise signature
            return new Response('Invalid signature', 400);
        }

        // Gère l’événement de paiement réussi
        if ($event->type === 'checkout.session.completed') {
            $session = $event->data->object;
            $orderId = $session->metadata->order_id ?? null;
            if ($orderId) {
                $order = $orderRepo->find($orderId);
                if ($order) {
                    $order->setStatus('payed');
                    // Ici tu peux aussi décrémenter le stock, envoyer un mail, etc.
                    $em->flush();
                }
            }
        }

        // Tu peux gérer d’autres events Stripe ici si besoin

        return new Response('OK', 200);
    }
}
