<?php

namespace App\Console\Commands;

use App\Rabbit\RabbitListener;
use App\Rabbit\RabbitMq;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use PhpAmqpLib\Message\AMQPMessage;

/** Demonio
 * Consumidor PUSH (equivalente a @RabbitListener de Spring).
 *
 * Diferencia con "queue:work":
 *   - queue:work    -> sondea con basic_get y espera jobs serializados de Laravel.
 *   - rabbit:listen -> se suscribe con basic_consume; RabbitMQ EMPUJA los
 *                      mensajes y aquí se manejan como eventos JSON planos.
 *
 * Uso:
 *   php artisan rabbit:listen "App\Rabbit\Listeners\OrderConfirmedListener"
 *   php artisan rabbit:listen "App\Rabbit\Listeners\OrderConfirmedListener" --max-messages=1
 *
 * En producción mantenlo vivo con supervisor/systemd (o un contenedor).
 */
class RabbitListen extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rabbit:listen
        {listener : Clase que implementa App\Rabbit\RabbitListener}
        {--prefetch=10 : Mensajes en vuelo sin ack por consumidor}
        {--max-messages=0 : Sale tras N mensajes (0 = indefinido)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Consumidor push de eventos RabbitMQ (equivalente a @RabbitListener)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $class = $this->argument('listener');

        if (! class_exists($class) || ! is_subclass_of($class, RabbitListener::class)) {
            $this->error("La clase [$class] no implementa ".RabbitListener::class);

            return self::FAILURE;
        }

        /** @var RabbitListener $listener */
        $listener = $this->laravel->make($class);

        // Bucle de reconexión: si se cae el broker o la red, reintenta.
        while (true) {
            try {
                $this->consume($listener);

                return self::SUCCESS;
            } catch (\Throwable $e) {
                $this->error('Listener caído: '.$e->getMessage());
                Log::error('rabbit:listen', [
                    'listener' => $class,
                    'error' => $e->getMessage(),
                ]);

                $this->warn('Reconectando en 5s...');
                sleep(5);
            }
        }
    }

    /**
     * Abre conexión, declara topología y consume en modo push.
     */
    private function consume(RabbitListener $listener): void
    {
        $queue = $listener::queue();
        $routingKey = $listener::routingKey();
        $exchange = RabbitMq::exchange();

        $connection = RabbitMq::connection();
        $channel = $connection->channel();

        // configurar y garantizar la infraestructura necesaria en el servidor de RabbitMQ
        RabbitMq::declareTopology($channel, $queue, $routingKey);

        // Cuántos mensajes sin ack puede tener en vuelo este consumidor.
        $channel->basic_qos(0, (int) $this->option('prefetch'), false);

        $max = (int) $this->option('max-messages');
        $processed = 0;

        $this->info("Escuchando queue=[{$queue}] exchange=[{$exchange}] rk=[{$routingKey}]");

        // Ctrl+C / SIGTERM: cierre ordenado.
        pcntl_async_signals(true);
        foreach ([SIGINT, SIGTERM] as $signal) {
            pcntl_signal($signal, function () use ($channel) {
                $this->warn('Señal recibida, deteniendo listener...');
                $channel->stopConsume();
            });
        }

        // vendor/php-amqplib/php-amqplib/PhpAmqpLib/Channel/AMQPChannel.php:986
        $channel->basic_consume(
            queue: $queue,
            consumer_tag: '',
            no_local: false,
            no_ack: false,
            exclusive: false,
            nowait: false,
            callback: function (AMQPMessage $message) use ($listener, $channel, &$processed, $max) {
                $payload = json_decode($message->getBody(), true);

                if (! is_array($payload)) {
                    $this->error('Payload no es JSON válido. Descartando.');
                    $message->nack(false, false);

                    return;
                }

                try {
                    $listener->handle($payload);
                    $message->ack();
                } catch (\Throwable $e) {
                    $this->error('Error en el listener: ' . $e->getMessage());
                    Log::error('rabbit:listen handler', [
                        'payload' => $payload,
                        'error' => $e->getMessage(),
                    ]);
                    // Descarta para no entrar en bucle. Para reintentos usa DLX.
                    $message->nack(false, false);
                }

                $processed++;

                if ($max > 0 && $processed >= $max) {
                    $channel->stopConsume();
                }
            }
        );

        // Bloquea aquí: consume() retorna cuando se llama stopConsume().
        $channel->consume();

        $channel->close();
        
        $connection->close();

        $this->info("Listener detenido. Mensajes procesados: {$processed}");
    }
}
