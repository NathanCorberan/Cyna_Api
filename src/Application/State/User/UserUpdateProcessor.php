<?php
namespace App\Application\State\User;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\User\UserUpdateDto;
use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Doctrine\ORM\EntityManagerInterface;

class UserUpdateProcessor implements ProcessorInterface
{
    public function __construct(
        private Security $security,
        private EntityManagerInterface $em
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): User
    {
        /** @var User $user */
        $user = $this->security->getUser();

        if ($data instanceof UserUpdateDto) {
            if ($data->first_name !== null) {
                $user->setFirstName($data->first_name);
            }
            if ($data->last_name !== null) {
                $user->setLastName($data->last_name);
            }

            $this->em->persist($user);
            $this->em->flush();
        }

        return $user;
    }
}
