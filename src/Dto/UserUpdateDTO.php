<?php
// src/Dto/UserUpdateDTO.php
namespace App\Dto;

use Symfony\Component\Serializer\Annotation\Groups;

final class UserUpdateDTO
{
    #[Groups(['user:update'])]
    public ?string $first_name = null;

    #[Groups(['user:update'])]
    public ?string $last_name = null;
}
