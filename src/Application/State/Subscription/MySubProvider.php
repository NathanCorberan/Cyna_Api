<?php
namespace App\Application\State\Subscription;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Symfony\Bundle\SecurityBundle\Security;
use App\Repository\SubscriptionRepository;
use App\Dto\Subscription\MySubOutputDto;
use App\Entity\User;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Request;

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

        // Récupération du paramètre "lang" depuis la requête
        /** @var Request|null $request */
        $request = $context['request'] ?? null;
        $lang = $request?->query->get('lang', 'fr'); // défaut = 'fr'
        $lang = strtolower($lang); // sécurité : toujours en minuscule

        // Récupère toutes les souscriptions "available" pour l'utilisateur
        $subscriptions = $this->subscriptionRepository->findSubByMe($user->getId());

        foreach ($subscriptions as $sub) {
            $dto = new MySubOutputDto();
            $dto->id = $sub->getId();
            $dto->startDate = $sub->getStartDate();
            $dto->endDate = $sub->getEndDate();
            $dto->status = $sub->getStatus();

            // Type
            $type = $sub->getSubscriptionType();
            $dto->type = $type ? $type->getType() : '';
            $dto->price = $type ? (float)$type->getPrice() : 0.0;

            // Produit
            $product = $type ? $type->getProduct() : null;
            if ($product) {
                $dto->productTitle = '';
                $dto->productDescription = null;

                foreach ($product->getProductLangages() as $productLang) {
                    if (strtolower($productLang->getCode()) === $lang) {
                        $dto->productTitle = $productLang->getName();
                        $dto->productDescription = $productLang->getDescription();
                        break;
                    }
                }

                $images = $product->getProductImages();
                $dto->productImage = count($images) > 0 ? $images[0]->getImageLink() : null;
            } else {
                $dto->productTitle = '';
                $dto->productImage = null;
                $dto->productDescription = null;
            }

            yield $dto;
        }
    }
}
