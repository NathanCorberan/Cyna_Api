<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Stripe\Stripe;
use Stripe\SetupIntent;

class StripeCancelSetupIntentController extends AbstractController
{
    #[Route('/api/payment/setup-intent/cancel/{id}', name: 'stripe_cancel_setup_intent', methods: ['POST'])]
    public function __invoke($id)
    {
        Stripe::setApiKey($_ENV['STRIPE_SECRET_KEY']);

        $setupIntent = SetupIntent::retrieve($id);
        $setupIntent->cancel();

        return $this->json([
            'status' => 'cancelled',
            'setup_intent_id' => $id
        ]);
    }
}
