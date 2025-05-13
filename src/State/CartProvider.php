<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\OrderItem;
use App\Repository\OrderItemRepository;
use App\Repository\OrderRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RequestStack;

class CartProvider implements ProviderInterface
{
    public function __construct(
        private OrderRepository $orderRepository,
        private OrderItemRepository $orderItemRepository,
        private Security $security,
        private RequestStack $requestStack
    ) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): iterable
    {
        $user = $this->security->getUser();
        $request = $this->requestStack->getCurrentRequest();
        $cartToken = $request?->headers->get('X-Cart-Token');

        $order = null;

        if ($user) {
            $order = $this->orderRepository->findOneBy(['user' => $user, 'status' => 'cart']);
        } elseif ($cartToken) {
            $order = $this->orderRepository->findOneBy(['cartToken' => $cartToken, 'status' => 'cart']);
        }

        if (!$order) {
            return [];
        }

        return $this->orderItemRepository->findBy(['order' => $order]);
    }
}
