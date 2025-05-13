<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Order;
use App\Entity\OrderItem;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class OrderDataPersister implements ProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ProcessorInterface $decoratedProcessor
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        if (!$data instanceof Order) {
            return $this->decoratedProcessor->process($data, $operation, $uriVariables, $context);
        }

        // Associer User par ID si non déjà associé
        if ($data->getUserId() !== null && $data->getUser() === null) {
            $user = $this->entityManager->getRepository(User::class)->find($data->getUserId());
            if (!$user) {
                throw new NotFoundHttpException("User with ID '{$data->getUserId()}' not found.");
            }
            $data->setUser($user);
        }

        // Associer OrderItems par ID si non déjà associés
        if (!empty($data->getOrderItemsId()) && $data->getOrderItems()->count() === 0) {
            foreach ($data->getOrderItemsId() as $itemId) {
                $orderItem = $this->entityManager->getRepository(OrderItem::class)->find($itemId);
                if (!$orderItem) {
                    throw new NotFoundHttpException("OrderItem with ID '$itemId' not found.");
                }
                $data->addOrderItem($orderItem);
            }
        }

        // Recalcul automatique du total
        $data->recalculateTotalAmount();

        return $this->decoratedProcessor->process($data, $operation, $uriVariables, $context);
    }
}
