<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\OrderItemPatchInput;
use App\Entity\OrderItem;
use App\Repository\OrderItemRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class OrderItemPatchState implements ProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private OrderItemRepository $orderItemRepository,
        private Security $security,
        private RequestStack $requestStack
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): OrderItem
    {
        if (!$data instanceof OrderItemPatchInput) {
            throw new \InvalidArgumentException('Expected OrderItemPatchInput DTO.');
        }

        $id = $uriVariables['id'] ?? null;
        $orderItem = $this->orderItemRepository->find($id);

        if (!$orderItem) {
            throw new NotFoundHttpException("OrderItem #$id not found.");
        }

        $user = $this->security->getUser();
        $request = $this->requestStack->getCurrentRequest();
        $cartToken = $request?->headers->get('X-Cart-Token');

        $order = $orderItem->getOrder();

        if (
            ($user && $order->getUser()?->getId() !== $user->getId()) ||
            (!$user && $cartToken !== $order->getCartToken())
        ) {
            throw new AccessDeniedHttpException("You are not allowed to modify this item.");
        }

        if ($data->quantity !== null) {
            $orderItem->setQuantity($data->quantity);
        }

        $this->entityManager->flush();

        return $orderItem;
    }
}