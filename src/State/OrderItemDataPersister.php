<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\OrderItemInput;
use App\Entity\OrderItem;
use App\Repository\ProductRepository;
use App\Repository\SubscriptionTypeRepository;
use App\Repository\OrderItemRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use App\Dto\CreateCartInput;
use App\State\CreateCartProcessor;
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
        if (!$data instanceof OrderItemInput) {
            throw new \InvalidArgumentException('Expected OrderItemInput DTO.');
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

        $cartInput = new CreateCartInput();
        $cartInput->key = $cartToken;

        $cart = $this->createCartProcessor->process($cartInput, $operation, $uriVariables, $context);

        $subscriptionType = $this->subscriptionTypeRepository->findOneBy(['product' => $product]);
        if (!$subscriptionType) {
            throw new NotFoundHttpException("No subscription type found for product ID {$data->product_id}.");
        }

        // ✅ FUSION : si un OrderItem existe déjà pour ce produit, on cumule les quantités
        $existingItem = $this->orderItemRepository->findOneBy([
            'order' => $cart,
            'product' => $product
        ]);

        if ($existingItem) {
            $existingItem->setQuantity($existingItem->getQuantity() + $data->quantity);

            if ($existingItem->getUnitPrice() !== $subscriptionType->getPrice()) {
                $existingItem->setUnitPrice($subscriptionType->getPrice());
            }

            $this->entityManager->flush();
            return $existingItem;
        }

        // Sinon création normale
        $orderItem = new OrderItem();
        $orderItem->setOrder($cart);
        $orderItem->setProduct($product);
        $orderItem->setUnitPrice($subscriptionType->getPrice());
        $orderItem->setQuantity($data->quantity);

        $this->entityManager->persist($orderItem);
        $this->entityManager->flush();

        return $orderItem;
    }
}
