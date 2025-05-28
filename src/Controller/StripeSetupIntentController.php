<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Stripe;
use Stripe\Customer;
use Stripe\SetupIntent;

class StripeSetupIntentController extends AbstractController
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    #[Route('/api/payment/setup-intent', name: 'stripe_setup_intent', methods: ['POST'])]
    public function __invoke(Request $request)
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->json(['error' => 'User not authenticated'], 401);
        }

        try {
            Stripe::setApiKey($_ENV['STRIPE_SECRET_KEY']);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Stripe API key missing or invalid', 'details' => $e->getMessage()], 500);
        }

        try {
            if (!$user->getStripeCustomerId()) {
                $customer = Customer::create([
                    'email' => $user->getEmail(),
                    'name'  => $user->getFirstName() . ' ' . $user->getLastName(),
                ]);
                $user->setStripeCustomerId($customer->id);
                $this->em->flush();
            }

            $data = json_decode($request->getContent(), true);
            $orderId = $data['order_id'] ?? null;

            $setupIntent = SetupIntent::create([
                'customer' => $user->getStripeCustomerId(),
                'payment_method_types' => ['card'],
                'metadata' => [
                    'order_id' => $orderId
                ]
            ]);

            return $this->json([
                'client_secret' => $setupIntent->client_secret,
                'setup_intent_id' => $setupIntent->id,
            ]);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }
}
