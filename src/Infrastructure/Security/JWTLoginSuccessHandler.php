<?php

namespace App\Infrastructure\Security;

use App\Repository\OrderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\User\UserInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Events;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class JWTLoginSuccessHandler implements EventSubscriberInterface
{
    public function __construct(
        private RequestStack $requestStack,
        private OrderRepository $orderRepository,
        private EntityManagerInterface $entityManager,
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            Events::AUTHENTICATION_SUCCESS => 'onAuthenticationSuccess',
        ];
    }

    public function onAuthenticationSuccess(AuthenticationSuccessEvent $event): void
    {
        $request = $this->requestStack->getCurrentRequest();
        $token = $request?->headers->get('X-Cart-Token');

        $user = $event->getUser();
        if (!$user instanceof UserInterface || !$token) {
            return;
        }

        $order = $this->orderRepository->findOneBy(['cartToken' => $token]);

        if ($order && !$order->getUser()) {
            $order->setUser($user);
            $this->entityManager->flush();
        }
    }
}
