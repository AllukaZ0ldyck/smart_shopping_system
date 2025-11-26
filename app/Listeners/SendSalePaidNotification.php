<?php

namespace App\Listeners;

use App\Events\SalePaidEvent;
use Illuminate\Support\Facades\Log;

class SendSalePaidNotification
{
    /**
     * Handle the event.
     */
    public function handle(SalePaidEvent $event)
    {
        $sale = $event->sale;

        // Example actions:
        Log::info("Sale Paid Event Fired", [
            'sale_id' => $sale->id,
            'reference' => $sale->reference_code,
            'amount' => $sale->grand_total,
            'paid_amount' => $sale->paid_amount,
        ]);

        /**
         * If you want:
         *  - trigger auto-printing
         *  - send websocket message
         *  - notify frontend
         *  - update queue / logs
         * add logic here.
         *
         * Example: send websocket event (Laravel Echo / Pusher)
         *
         * event(new \App\Events\PrintReceiptEvent($sale));
         */
    }
}
