<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Stripe\Stripe;
use Stripe\Customer;
use Stripe\SetupIntent;

class StripeSetupIntentController extends AbstractController
{
    #[Route('/api/payment/setup-intent', name: 'stripe_setup_intent', methods: ['POST'])]
    public function __invoke(Request $request)
    {
        $user = $this->getUser();
        Stripe::setApiKey($_ENV['STRIPE_SECRET_KEY']);

        if (!$user->getStripeCustomerId()) {
            $customer = Customer::create([
                'email' => $user->getEmail(),
                'name'  => $user->getFirstName() . ' ' . $user->getLastName(),
            ]);
            $user->setStripeCustomerId($customer->id);
            $this->getDoctrine()->getManager()->flush();
        }

        $data = json_decode($request->getContent(), true);
        $orderId = $data['order_id'] ?? null;

        $setupIntent = SetupIntent::create([
            'customer' => $user->getStripeCustomerId(),
            'payment_method_types' => ['card'],
            'metadata' => [
                'order_id' => $orderId // Pour retrouver l'order plus tard si besoin
            ]
        ]);

        return $this->json([
            'client_secret' => $setupIntent->client_secret,
            'setup_intent_id' => $setupIntent->id,
        ]);
    }
}
