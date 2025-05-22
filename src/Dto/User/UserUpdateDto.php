<?php
// src/Dto/UserUpdateDTO.php
namespace App\Dto\User;

use Symfony\Component\Serializer\Annotation\Groups;

final class UserUpdateDto
{
    #[Groups(['user:update'])]
    public ?string $first_name = null;

    #[Groups(['user:update'])]
    public ?string $last_name = null;
}
