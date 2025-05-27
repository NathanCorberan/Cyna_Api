<?php

namespace App\Dto\Product;

class ProductImageOutputDto
{
    public int $id;
    public string $image_link;
    public string $name;

    public function __construct(
        int $id,
        string $image_link,
        string $name
    ) {
        $this->id = $id;
        $this->image_link = $image_link;
        $this->name = $name;
    }
}
