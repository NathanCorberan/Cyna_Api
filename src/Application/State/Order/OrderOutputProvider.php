<?php
namespace App\Application\State\Order;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Dto\Order\OrderOutputDto;
use App\Repository\OrderRepository;

class OrderOutputProvider implements ProviderInterface
{
    public function __construct(private OrderRepository $orderRepo) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): iterable
    {
        $orders = $this->orderRepo->findAll();

        foreach ($orders as $order) {
            $dto = new OrderOutputDto();
            $dto->id = 'ORD-' . str_pad((string) $order->getId(), 3, '0', STR_PAD_LEFT);
            $dto->customer = $order->getUser()?->getFullName() ?? 'Invité';
            $dto->email = $order->getUser()?->getEmail() ?? 'inconnu@example.com';
            $dto->date = $order->getOrderDate();
            $dto->amount = number_format((float) $order->getTotalAmount(), 2, ',', ' ') . ' €';
            $dto->status = $order->getStatus() === 'payed' ? 'completed' : 'cart';
            $dto->paymentMethod = 'Carte bancaire';
            yield $dto;
        }
    }
}
