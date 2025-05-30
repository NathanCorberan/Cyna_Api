<?php
namespace App\Application\State\Subscription;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Symfony\Bundle\SecurityBundle\Security;
use App\Repository\SubscriptionRepository;
use App\Dto\Subscription\MySubOutputDto;
use App\Entity\User;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class MySubProvider implements ProviderInterface
{
    private Security $security;
    private SubscriptionRepository $subscriptionRepository;

    public function __construct(Security $security, SubscriptionRepository $subscriptionRepository)
    {
        $this->security = $security;
        $this->subscriptionRepository = $subscriptionRepository;
    }

    /**
     * @return iterable<MySubOutputDto>
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): iterable
    {
        $user = $this->security->getUser();

        if (!$user instanceof User) {
            throw new NotFoundHttpException('User not found.');
        }

        // Récupère toutes les souscriptions "available" pour l'utilisateur
        $subscriptions = $this->subscriptionRepository->findSubByMe($user->getId());

        foreach ($subscriptions as $sub) {
            $dto = new MySubOutputDto();
            $dto->id = $sub->getId();
            $dto->startDate = $sub->getStartDate();
            $dto->endDate = $sub->getEndDate();
            $dto->status = $sub->getStatus();

            // Récupère le type d'abonnement
            $type = $sub->getSubscriptionType();
            $dto->type = $type ? $type->getType() : '';
            $dto->price = $type ? (float)$type->getPrice() : 0.0; // ← ici

            // Produit relié
            $product = $type ? $type->getProduct() : null;
            if ($product) {
                // Titre FR (via productLangages)
                $dto->productTitle = '';
                $dto->productDescription = null;
                foreach ($product->getProductLangages() as $lang) {
                    if ($lang->getCode() === 'FR') {
                        $dto->productTitle = $lang->getName();
                        $dto->productDescription = $lang->getDescription();
                        break;
                    }
                }
                // Première image trouvée
                $dto->productImage = null;
                $images = $product->getProductImages();
                if (count($images) > 0) {
                    $dto->productImage = $images[0]->getImageLink();
                }
            } else {
                $dto->productTitle = '';
                $dto->productImage = null;
                $dto->productDescription = null;
            }
            yield $dto;
        }
    }
}
