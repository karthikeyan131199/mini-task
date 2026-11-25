<?php

namespace App\Services;

use App\Exceptions\InsufficientStockException;
use App\Jobs\SendOrderConfirmationEmail;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderService
{
    /**
     * Create an order with concurrent stock safety.
     *
     * @param array $customerData ['name' => ..., 'email' => ...]
     * @param array $items Array of ['product_id' => int, 'quantity' => int]
     * @return Order
     * @throws InsufficientStockException
     */
    public function createOrder(array $customerData, array $items): Order
    {
        return DB::transaction(function () use ($customerData, $items) {
            // 1. Resolve or Create Customer
            $customer = Customer::firstOrCreate(
                ['email' => strtolower(trim($customerData['email']))],
                ['name' => trim($customerData['name'])]
            );

            // Update customer name if provided and changed
            if (!empty($customerData['name']) && $customer->name !== trim($customerData['name'])) {
                $customer->update(['name' => trim($customerData['name'])]);
            }

            // 2. Aggregate quantities by product_id if the client passed duplicate product lines
            $aggregatedItems = [];
            foreach ($items as $item) {
                $pId = (int)$item['product_id'];
                $qty = (int)$item['quantity'];
                if ($qty <= 0) {
                    continue;
                }
                $aggregatedItems[$pId] = ($aggregatedItems[$pId] ?? 0) + $qty;
            }

            if (empty($aggregatedItems)) {
                throw new \InvalidArgumentException("Order must have at least one valid product line with quantity > 0.");
            }

            // Sort keys to prevent database deadlocks when acquiring pessimistic row locks
            ksort($aggregatedItems);
            $productIds = array_keys($aggregatedItems);

            // 3. Acquire pessimistic write lock (SELECT ... FOR UPDATE)
            $products = Product::whereIn('id', $productIds)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            // 4. Validate all products exist and verify stock availability
            $orderLinesData = [];
            $computedSubtotal = 0.00;
            $computedTaxTotal = 0.00;

            foreach ($aggregatedItems as $productId => $qtyRequired) {
                /** @var Product|null $product */
                $product = $products->get($productId);

                if (!$product) {
                    throw new \InvalidArgumentException("Product with ID {$productId} does not exist.");
                }

                if ($product->stock_on_hand < $qtyRequired) {
                    throw new InsufficientStockException(
                        "Insufficient stock for '{$product->name}' (Code: {$product->code}). Available: {$product->stock_on_hand}, Requested: {$qtyRequired}."
                    );
                }

                // Compute financial calculations
                $unitPrice = (float)$product->price_per_unit;
                $taxRate = (float)$product->tax_percentage;

                $lineSubtotal = round($unitPrice * $qtyRequired, 2);
                $lineTax = round($lineSubtotal * ($taxRate / 100), 2);
                $lineTotal = round($lineSubtotal + $lineTax, 2);

                $computedSubtotal += $lineSubtotal;
                $computedTaxTotal += $lineTax;

                $orderLinesData[] = [
                    'product' => $product,
                    'quantity' => $qtyRequired,
                    'unit_price' => $unitPrice,
                    'tax_percentage' => $taxRate,
                    'line_subtotal' => $lineSubtotal,
                    'line_tax' => $lineTax,
                    'line_total' => $lineTotal,
                ];
            }

            $computedGrandTotal = round($computedSubtotal + $computedTaxTotal, 2);

            // 5. Deduct Stock safely within the transaction
            foreach ($orderLinesData as $line) {
                /** @var Product $product */
                $product = $line['product'];
                $product->decrement('stock_on_hand', $line['quantity']);
            }

            // 6. Create Order Header
            $order = Order::create([
                'order_number' => 'ORD-' . strtoupper(Str::random(4)) . '-' . rand(1000, 9999),
                'customer_id' => $customer->id,
                'subtotal' => $computedSubtotal,
                'tax_amount' => $computedTaxTotal,
                'grand_total' => $computedGrandTotal,
                'status' => 'completed',
            ]);

            // 7. Create Order Item Lines
            foreach ($orderLinesData as $line) {
                /** @var Product $product */
                $product = $line['product'];
                $order->items()->create([
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'product_code' => $product->code,
                    'quantity' => $line['quantity'],
                    'unit_price' => $line['unit_price'],
                    'tax_percentage' => $line['tax_percentage'],
                    'line_subtotal' => $line['line_subtotal'],
                    'line_tax' => $line['line_tax'],
                    'line_total' => $line['line_total'],
                ]);
            }

            // 8. Dispatch Async Email Job after commit
            DB::afterCommit(function () use ($order) {
                SendOrderConfirmationEmail::dispatch($order);
            });

            return $order->load(['customer', 'items']);
        });
    }
}
