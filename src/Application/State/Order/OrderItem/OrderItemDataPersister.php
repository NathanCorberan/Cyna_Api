<?php

namespace App\Application\State\Order\OrderItem;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\Order\OrderItemInputDto;
use App\Entity\OrderItem;
use App\Repository\ProductRepository;
use App\Repository\SubscriptionTypeRepository;
use App\Repository\OrderItemRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use App\Dto\Cart\CreateCartInputDto;
use App\Application\State\Cart\CreateCartProcessor;
use Symfony\Bundle\SecurityBundle\Security;

class OrderItemDataPersister implements ProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ProductRepository $productRepository,
        private SubscriptionTypeRepository $subscriptionTypeRepository,
        private OrderItemRepository $orderItemRepository,
        private CreateCartProcessor $createCartProcessor,
        private Security $security
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): OrderItem
    {
        if (!$data instanceof OrderItemInputDto) {
            throw new \InvalidArgumentException('Expected OrderItemInputDto DTO.');
        }

        if (!$data->product_id || !$data->quantity) {
            throw new \InvalidArgumentException('Missing product_id or quantity.');
        }

        $product = $this->productRepository->find($data->product_id);
        if (!$product) {
            throw new NotFoundHttpException("Product with ID {$data->product_id} not found.");
        }

        $request = $context['request'] ?? null;
        $cartToken = $request?->headers->get('X-Cart-Token');

        $cartInput = new CreateCartInputDto();
        $cartInput->key = $cartToken;

        /** @var \App\Entity\Order $cart */
        $cart = $this->createCartProcessor->process($cartInput, $operation, $uriVariables, $context);

        // Gestion du SubscriptionType via l'ID passé, ou fallback sur le premier trouvé
        if ($data->subscription_type_id) {
            $subscriptionType = $this->subscriptionTypeRepository->find($data->subscription_type_id);
            if (!$subscriptionType) {
                throw new NotFoundHttpException("SubscriptionType with ID {$data->subscription_type_id} not found.");
            }
            if ($subscriptionType->getProduct()->getId() !== $data->product_id) {
                throw new \InvalidArgumentException("SubscriptionType does not belong to the specified product.");
            }
        } else {
            $subscriptionType = $this->subscriptionTypeRepository->findOneBy(['product' => $product]);
            if (!$subscriptionType) {
                throw new NotFoundHttpException("No subscription type found for product ID {$data->product_id}.");
            }
        }

        // Recherche d'un OrderItem existant dans ce panier avec ce produit ET ce subscriptionType
        $existingItem = $this->orderItemRepository->findOneBy([
            'order' => $cart,
            'product' => $product,
            'subscriptionType' => $subscriptionType
        ]);

        if ($existingItem) {
            $existingItem->setQuantity($existingItem->getQuantity() + $data->quantity);

            if ($existingItem->getUnitPrice() !== $subscriptionType->getPrice()) {
                $existingItem->setUnitPrice($subscriptionType->getPrice());
            }

            $existingItem->setSubscriptionType($subscriptionType);

            $cart->recalculateTotalAmount();
            $this->entityManager->flush();

            return $existingItem;
        }

        // Nouveau OrderItem
        $orderItem = new OrderItem();
        $orderItem->setOrder($cart);
        $orderItem->setProduct($product);
        $orderItem->setUnitPrice($subscriptionType->getPrice());
        $orderItem->setQuantity($data->quantity);
        $orderItem->setSubscriptionType($subscriptionType);

        $this->entityManager->persist($orderItem);

        // Ajout dans la collection du panier
        $cart->addOrderItem($orderItem);

        $cart->recalculateTotalAmount();

        $this->entityManager->flush();

        return $orderItem;
    }
}
