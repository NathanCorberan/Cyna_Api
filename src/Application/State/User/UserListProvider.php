<?php
namespace App\Application\State\User;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Repository\UserRepository;
use App\Dto\User\UserListDto;

class UserListProvider implements ProviderInterface
{
    public function __construct(private UserRepository $userRepo) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): iterable
    {
        foreach ($this->userRepo->findAll() as $user) {
            $dto = new UserListDto();
            $dto->id = $user->getId();
            $dto->name = $user->getFirstName() . ' ' . $user->getLastName();
            $dto->email = $user->getEmail();
            $dto->role = strtolower(str_replace('ROLE_', '', $user->getRoles()[0] ?? 'user'));
            $dto->status = $user->isActivate() ? 'active' : 'inactive';
            $dto->orders = count($user->getOrders());

            yield $dto;
        }
    }
}
