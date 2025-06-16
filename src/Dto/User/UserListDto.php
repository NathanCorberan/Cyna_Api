<?php
namespace App\Dto\User;

use Symfony\Component\Serializer\Annotation\Groups;

final class UserListDto
{
    #[Groups(['User:list'])]
    public int $id;

    #[Groups(['User:list'])]
    public string $name;

    #[Groups(['User:list'])]
    public string $email;

    #[Groups(['User:list'])]
    public string $role;

    #[Groups(['User:list'])]
    public string $status;

    #[Groups(['User:list'])]
    public int $orders;
}
