<?php

namespace App\Console\Commands;

use App\Rabbit\Listeners\OrderConfirmedListener;
use App\Rabbit\RabbitMq;
use Illuminate\Console\Command;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * PRODUCTOR del evento OrderConfirmed.
 *
 * Publica JSON PLANO (no un Job de Laravel) en el exchange, para que lo
 * consuma el listener push y no "queue:work".
 *
 * Flujo:
 *   app:fire-order-confirmed
 *     -> exchange app_exchange (rk "order.confirmed")
 *     -> cola order-confirmed-queue
 *     -> rabbit:listen OrderConfirmedListener
 */
class FireOrderConfirmed extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fire-order-confirmed
        {orderNumber=ORD-1001 : Número de pedido a publicar}
        {--null-order : Publica orderNumber null para probar el descarte}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Publica un evento OrderConfirmed (JSON plano) en el exchange';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $queue = OrderConfirmedListener::queue(); // order-confirmed-queue (El casillero o ranura de correspondencia de una casa específica)
        $routingKey = OrderConfirmedListener::routingKey(); // order.confirmed (La etiqueta con la dirección exacta en el paquete)
        $exchange = RabbitMq::exchange(); // app_exchange (La central de correos principal de la ciudad)

        $connection = RabbitMq::connection(); // La carretera o autopista principal de red que conecta con el servidor
        $channel = $connection->channel(); // El carril virtual o ventanilla abierta dentro de esa autopista

        RabbitMq::declareTopology($channel, $queue, $routingKey); // El equipo de urbanismo: verifica y construye físicamente la central, el casillero y su ruta si no existen

        $payload = [
            'orderNumber' => $this->option('null-order')
                ? null
                : (string) $this->argument('orderNumber'),
            'status' => 'CONFIRMED',
            'occurredAt' => now()->toIso8601String(),
        ];

        $message = new AMQPMessage(json_encode($payload), [
            'content_type' => 'application/json',
            'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT, // 2
        ]);

        $channel->basic_publish($message, $exchange, $routingKey); // send

        $this->info("Publicado en exchange={$exchange} rk={$routingKey} -> queue={$queue}");
        $this->line(json_encode($payload, JSON_PRETTY_PRINT));

        $channel->close();
        $connection->close();

        return self::SUCCESS;
    }
}
