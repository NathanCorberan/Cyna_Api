<?php

namespace App\Dto\Carousel;

use Symfony\Component\HttpFoundation\File\UploadedFile;

final class CarouselMediaInput
{
    public ?UploadedFile $imageFile = null;
    public ?int $panel_order = null;
}
