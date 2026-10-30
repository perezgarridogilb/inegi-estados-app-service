<?php

namespace App\Rabbit\Listeners;

use App\Rabbit\Events\OrderConfirmedEvent;
use App\Rabbit\RabbitListener;
use Illuminate\Support\Facades\Log;

/**
 * Equivalente PHP de:
 *
 *   public class OrderEventsListener {
 *       private final OrderService orderService;
 *
 *       @RabbitListener(queues = "order-confirmed-queue")
 *       public void handleOrderConfirmed(OrderConfirmedEvent event) {
 *           if (event.orderNumber() == null) {
 *               log.error("OrderConfirmedEvent con orderNumber null. Descartando.");
 *               return;
 *           }
 *           orderService.updateOrderStatus(event.orderNumber(), OrderStatus.CONFIRMED);
 *       }
 *   }
 *
 * CONSUMIDOR de la cola "order-confirmed-queue".
 * PRODUCTOR de ejemplo: php artisan app:fire-order-confirmed
 *
 * Se ejecuta con:
 *   php artisan rabbit:listen "App\Rabbit\Listeners\OrderConfirmedListener"
 */
final class OrderConfirmedListener implements RabbitListener
{
    /**
     * @RabbitListener(queues = "order-confirmed-queue")
     */
    public static function queue(): string
    {
        return 'order-confirmed-queue';
    }

    /**
     * Binding @Bean.
     */
    public static function routingKey(): string
    {
        return 'order.confirmed';
    }

    /**
     * (equivalente a orderService.updateOrderStatus(...)).
     */
    public function handle(array $payload): void
    {
        // xdebug_break();
        $event = OrderConfirmedEvent::fromArray($payload);

        if ($event->orderNumber === '') {
            Log::error('❌ OrderConfirmedEvent con orderNumber null. Descartando.', [
                'payload' => $payload,
            ]);

            return;
        }

        Log::info('✅ OrderConfirmed: pedido confirmado', [
            'orderNumber' => $event->orderNumber,
            'status' => $event->status,
        ]);

        // TODO: reemplazar por tu servicio real, p. ej.:
        // app(OrderService::class)->updateOrderStatus($event->orderNumber, 'CONFIRMED');
    }
}
