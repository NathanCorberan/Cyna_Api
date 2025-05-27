<?php

namespace App\Application\State\Order;

use ApiPlatform\State\ProviderInterface;
use ApiPlatform\Metadata\Operation;
use App\Dto\Order\UserOrderOutputDto;
use App\Dto\Order\UserOrderProductDto;
use App\Repository\OrderRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class UserOrdersProvider implements ProviderInterface
{
    public function __construct(
        private Security $security,
        private OrderRepository $orderRepository,
    ) {}

    /**
     * @param Operation $operation
     * @param array $uriVariables
     * @param array $context
     * @return UserOrderOutputDto[]
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        $user = $this->security->getUser();
        if (!$user) {
            throw new AccessDeniedException('User not authenticated');
        }

        $orders = $this->orderRepository->findBy([
            'user' => $user,
            'status' => 'payed',
        ], ['order_date' => 'DESC']);

        $results = [];
        foreach ($orders as $order) {
            $dto = new UserOrderOutputDto();
            $dto->id = $order->getId();
            $dto->orderDate = $order->getOrderDate();
            $dto->status = $order->getStatus();
            $dto->totalAmount = $order->getTotalAmount();
            $dto->trackingNumber = $order->getCartToken();

            foreach ($order->getOrderItems() as $orderItem) {
                $prodDto = new UserOrderProductDto();
                $prodDto->id = $orderItem->getProduct()->getId();
                $prodDto->name = $orderItem->getProduct()->getNameForLocale('fr');
                $prodDto->quantity = $orderItem->getQuantity();
                $prodDto->totalPrice = $orderItem->getTotalPrice();
                $dto->products[] = $prodDto;
            }
            $results[] = $dto;
        }
        return $results;
    }
}
