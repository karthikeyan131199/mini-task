<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    /**
     * Fetch customer order history by email.
     */
    public function orderHistory(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'string', 'email'],
        ]);

        $email = strtolower(trim($request->input('email')));
        $customer = Customer::where('email', $email)->first();

        if (!$customer) {
            return response()->json([
                'success' => true,
                'found' => false,
                'customer' => null,
                'orders' => [],
            ]);
        }

        $orders = $customer->orders()
            ->with('items')
            ->orderBy('id', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'found' => true,
            'customer' => [
                'id' => $customer->id,
                'name' => $customer->name,
                'email' => $customer->email,
            ],
            'orders' => $orders,
        ]);
    }

    /**
     * Search customer by email for auto-fill on POS counter.
     */
    public function search(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'string'],
        ]);

        $email = strtolower(trim($request->input('email')));
        $customer = Customer::where('email', $email)->first();

        return response()->json([
            'found' => (bool)$customer,
            'customer' => $customer ? [
                'id' => $customer->id,
                'name' => $customer->name,
                'email' => $customer->email,
            ] : null,
        ]);
    }
}
