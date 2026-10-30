<?php

namespace App\Rabbit;

use PhpAmqpLib\Connection\AMQPStreamConnection;

/**
 * Punto único de conexión AMQP para los listeners de eventos.
 *
 * Reutiliza la conexión "rabbitmq-events" de config/queue.php
 *
 * NOTA: esto es AMQP puro (eventos JSON planos), NO el driver de colas de Laravel.
 *       Por eso no usa enqueue/dequeue ni el envelope serializado de los jobs.
 */
final class RabbitMq
{
    /**
     * Configuración de la conexión con exchange.
     */
    public static function config(): array
    {
        return config('queue.connections.rabbitmq-events', []);
    }

    /**
     * Exchange al que se publica / desde el que se enruta.
     */
    public static function exchange(): string
    {
        return self::config()['options']['queue']['exchange'];
    }

    /**
     * Tipo de exchange (direct por defecto).
     */
    public static function exchangeType(): string
    {
        return self::config()['options']['queue']['exchange_type'];
    }

    /**
     * Abre una conexión AMQP usando el primer host configurado.
     */
    public static function connection(): AMQPStreamConnection
    {
        $host = self::config()['hosts'][0] ?? [];

        return new AMQPStreamConnection(
            $host['host'],
            (int) $host['port'],
            $host['user'],
            $host['password'],
            $host['vhost'],
        );
    }

    /**
     * Declara exchange, cola y binding (idempotente).
     *
     * En Spring esto equivale a tus @Bean Queue/Exchange/Binding.
     * El paquete de Laravel declara el exchange al publicar, pero NO bindea
     * la cola, por eso aquí se hace explícito.
     */
    public static function declareTopology(
        \PhpAmqpLib\Channel\AMQPChannel $channel,
        string $queue,
        string $routingKey,
    ): void {
        $channel->exchange_declare(self::exchange(), self::exchangeType(), false, true, false);
        $channel->queue_declare($queue, false, true, false, false);
        $channel->queue_bind($queue, self::exchange(), $routingKey);
    }
}
