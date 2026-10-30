<?php

namespace App\Rabbit;

/**
 * Contrato de un listener de eventos RabbitMQ (equivalente a @RabbitListener).
 *
 * Cada clase concreta declara su cola y su routing key, igual que:
 *
 *   @RabbitListener(queues = "order-confirmed-queue")
 *   public void handleOrderConfirmed(OrderConfirmedEvent event)
 *
 * La diferencia con un Job de Laravel:
 *   - Un Job se consume con "php artisan queue:work" (polling con basic_get).
 *   - Un listener se consume con "php artisan rabbit:listen" (push con basic_consume).
 */
interface RabbitListener
{
    /**
     * Cola que este listener consume.
     */
    public static function queue(): string;

    /**
     * Routing key con la que se bindea la cola al exchange.
     */
    public static function routingKey(): string;

    /**
     * Procesa un evento ya decodificado (array asociativo del JSON).
     *
     * Si lanza excepción, el mensaje se descarta (nack sin requeue) y se loguea.
     */
    public function handle(array $payload): void;
}
