<?php

namespace App\Jobs;

use App\Models\Order;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SendOrderConfirmationEmail implements ShouldQueue
{
    use Queueable;

    public Order $order;

    /**
     * Create a new job instance.
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $order = $this->order->load(['customer', 'items']);
        $customer = $order->customer;

        Log::info("Sending order confirmation email to {$customer->email} for Order #{$order->order_number}", [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'customer_name' => $customer->name,
            'customer_email' => $customer->email,
            'grand_total' => $order->grand_total,
            'items_count' => $order->items->count(),
            'dispatched_at' => now()->toIso8601String(),
        ]);
    }
}
