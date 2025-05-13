<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\OrderItem;
use App\Repository\OrderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class SecureOrderItemDeletionProcessor implements ProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private OrderRepository $orderRepository,
        private Security $security,
        private RequestStack $requestStack
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): ?OrderItem
    {
        if (!$data instanceof OrderItem) {
            throw new \InvalidArgumentException('Expected OrderItem instance');
        }

        $user = $this->security->getUser();
        $request = $this->requestStack->getCurrentRequest();
        $cartToken = $request?->headers->get('X-Cart-Token');

        $order = $data->getOrder();

        // Refuser la suppression si le panier ne correspond pas à l'utilisateur ou au token
        if (
            ($user && $order->getUser()?->getId() !== $user->getId()) ||
            (!$user && $cartToken !== $order->getCartToken())
        ) {
            throw new AccessDeniedHttpException("You do not have permission to delete this item.");
        }

        $this->entityManager->remove($data);
        $this->entityManager->flush();

        return null;
    }
}
