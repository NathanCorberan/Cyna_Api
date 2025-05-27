<?php
namespace App\Dto\Product;
use ApiPlatform\Metadata\ApiResource;

#[ApiResource]
class ProductDetailsOutputDto
{
    public int $id;
    public int $available_stock;
    public string $creation_date;
    public string $status;
    public string $category_name;

    /** @var ProductLangageOutputDto[] */
    public array $productLangages;

    /** @var ProductImageOutputDto[] */
    public array $productImages;

    /** @var SubscriptionTypeOutputDto[] */
    public array $subscriptionTypes;

    public function __construct(
        int $id,
        int $available_stock,
        string $creation_date,
        string $status,
        string $category_name,
        array $productLangages,
        array $productImages,
        array $subscriptionTypes
    ) {
        $this->id = $id;
        $this->available_stock = $available_stock;
        $this->creation_date = $creation_date;
        $this->status = $status;
        $this->category_name = $category_name;
        $this->productLangages = $productLangages;
        $this->productImages = $productImages;
        $this->subscriptionTypes = $subscriptionTypes;
    }
}
