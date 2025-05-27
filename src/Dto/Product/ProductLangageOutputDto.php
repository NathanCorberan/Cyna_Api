<?php

namespace App\Dto\Product;

class ProductLangageOutputDto
{
    public int $id;
    public string $code;
    public string $name;
    public string $description;

    public function __construct(
        int $id,
        string $code,
        string $name,
        string $description
    ) {
        $this->id = $id;
        $this->code = $code;
        $this->name = $name;
        $this->description = $description;
    }
}
