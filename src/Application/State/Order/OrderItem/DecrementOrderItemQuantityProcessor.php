<?php
namespace App\Application\State\Order\OrderItem;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\OrderItem;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class DecrementOrderItemQuantityProcessor implements ProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $em,
        private Security $security
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): OrderItem
    {
        $request = $context['request'] ?? null;
        $cartToken = $request?->headers->get('X-Cart-Token');
        $user = $this->security->getUser();

        $orderItemId = $uriVariables['id'] ?? null;
        if (!$orderItemId) {
            throw new \InvalidArgumentException('OrderItem ID manquant dans l’URI.');
        }

        $orderItem = $this->em->getRepository(OrderItem::class)->find($orderItemId);
        if (!$orderItem) {
            throw new NotFoundHttpException('OrderItem non trouvé.');
        }

        $order = $orderItem->getOrder();

        if ($user && $order->getUser()?->getId() === $user->getId()) {
        } elseif ($cartToken && $order->getCartToken() === $cartToken) {
        } else {
            throw new AccessDeniedHttpException('Vous devez être connecté ou fournir un X-Cart-Token valide.');
        }

        if ($orderItem->getQuantity() <= 1) {
            throw new \RuntimeException("Impossible de diminuer la quantité en dessous de 1.");
        }

        $orderItem->setQuantity($orderItem->getQuantity() - 1);
        $orderItem->setTotalPrice($orderItem->getQuantity() * $orderItem->getUnitPrice());
        $order->recalculateTotalAmount();

        $this->em->flush();

        return $orderItem;
    }
}
