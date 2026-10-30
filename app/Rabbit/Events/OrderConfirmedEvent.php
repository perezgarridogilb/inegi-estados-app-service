<?php

namespace App\Rabbit\Events;

/**
 * Evento OrderConfirmed (DTO).
 *
 * Es el equivalente a tu record OrderConfirmedEvent de Java: no es un Job
 * de Laravel, es un evento que viaja como JSON plano por RabbitMQ.
 */
final readonly class OrderConfirmedEvent
{
    public function __construct(
        public string $orderNumber,
        public string $status,
    ) {
    }

    /**
     * Hidrata el evento desde el JSON recibido.
     *
     * Si "orderNumber" viene null/ausente se normaliza a "" para que el
     * listener pueda descartarlo de forma explícita (como en Java con null).
     */
    public static function fromArray(array $payload): self
    {
        return new self(
            orderNumber: (string) ($payload['orderNumber'] ?? ''),
            status: (string) ($payload['status'] ?? 'CONFIRMED'),
        );
    }
}
