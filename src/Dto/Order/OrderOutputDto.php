<?php
namespace App\Dto\Order;

use Symfony\Component\Serializer\Annotation\Groups;

final class OrderOutputDto
{
    #[Groups(['Order:output'])]
    public string $id;

    #[Groups(['Order:output'])]
    public string $customer;

    #[Groups(['Order:output'])]
    public string $email;

    #[Groups(['Order:output'])]
    public string $date;

    #[Groups(['Order:output'])]
    public string $amount;

    #[Groups(['Order:output'])]
    public string $status;

    #[Groups(['Order:output'])]
    public string $paymentMethod;
}
