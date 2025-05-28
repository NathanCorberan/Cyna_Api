<?php

namespace App\Service;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Stripe;
use Stripe\Customer;

class StripeCustomerManager
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
        Stripe::setApiKey($_ENV['STRIPE_SECRET_KEY']);
    }

    public function getOrCreateStripeCustomer(User $user): string
    {
        if ($user->getStripeCustomerId()) {
            return $user->getStripeCustomerId();
        }

        $customer = Customer::create([
            'email' => $user->getEmail(),
            'name'  => trim($user->getFirstName() . ' ' . $user->getLastName()),
        ]);

        $user->setStripeCustomerId($customer->id);
        $this->em->flush();

        return $customer->id;
    }
}
