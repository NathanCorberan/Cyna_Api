<?php

namespace App\Application\State\Checkout;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Order;
use App\Repository\OrderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CheckoutState implements ProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private OrderRepository $orderRepository,
        private Security $security,
        private RequestStack $requestStack
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): Order
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
            throw new NotFoundHttpException('No active cart found.');
        }

        if ($order->getOrderItems()->isEmpty()) {
            throw new BadRequestHttpException("Cannot checkout an empty cart.");
        }

        $order->recalculateTotalAmount();
        $order->setStatus('payed');
        $this->entityManager->flush();

        return $order;
    }
}
