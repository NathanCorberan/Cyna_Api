<?php

namespace App\State;

use App\Entity\Order;
use App\Dto\CreateCartInput;
use App\Repository\OrderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class CreateCartProcessor implements ProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private OrderRepository $orderRepository,
        private Security $security,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): Order
    {
        /** @var CreateCartInput $data */

        $user = $this->security->getUser();
        $cartToken = $data->key ?? null;

        // === Cas 3 : connecté avec panier existant (lié au user)
        if ($user) {
            $cart = $this->orderRepository->findOneBy(['user' => $user, 'status' => 'cart']);
            if ($cart) {
                if ($cartToken && !$cart->getCartToken()) {
                    $cart->setCartToken($cartToken);
                    $this->entityManager->flush();
                }
                return $cart;
            }
        }

        // === Cas 4 : non connecté, mais panier existe via cartToken
        if (!$user && $cartToken) {
            $cart = $this->orderRepository->findOneBy(['cartToken' => $cartToken, 'status' => 'cart']);
            if ($cart) {
                return $cart;
            }
        }

        // === Cas hybride : token fourni ET existe déjà => on récupère
        if ($cartToken) {
            $existingByToken = $this->orderRepository->findOneBy(['cartToken' => $cartToken, 'status' => 'cart']);
            if ($existingByToken) {
                if ($user && !$existingByToken->getUser()) {
                    $existingByToken->setUser($user);
                    $this->entityManager->flush();
                }
                return $existingByToken;
            }
        }

        // === Cas 1 ou 2 : aucun panier existant, on crée un nouveau
        $newCart = new Order();
        $newCart->setStatus('cart');
        $newCart->setOrderDate(date('Y-m-d'));
        $newCart->setTotalAmount("0.00");

        if ($user) {
            $newCart->setUser($user);
        }

        if (!$user && !$cartToken) {
            $cartToken = bin2hex(random_bytes(16));
        }

        if ($cartToken) {
            $newCart->setCartToken($cartToken);
        }

        $this->entityManager->persist($newCart);
        $this->entityManager->flush();

        return $newCart;
    }
}

// 1cas
 // il est pas connecté et il a pas de pannier -> ni jwt ni cart token -> creer commande avec cart token sans user
// 2cas
 //il est connecté et il a pas de pannier -> jwt -> creer commande user
// 3cas
 // il est connecté et il a un pannier -> jwt -> on met a jour le pannier cart avec le user 
// 4cas
 // il est pas connecté et il a un pannier -> cart token -> on met a jour le pannier cart avec le cart token