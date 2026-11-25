<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'customer_email' => ['required', 'string', 'email', 'max:255'],
            'customer_name' => ['required', 'string', 'max:255'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ];
    }

    /**
     * Custom messages for validation.
     */
    public function messages(): array
    {
        return [
            'customer_email.required' => 'Customer email is required.',
            'customer_email.email' => 'Please provide a valid email address.',
            'customer_name.required' => 'Customer name is required.',
            'items.required' => 'At least one product line is required to place an order.',
            'items.min' => 'At least one product line is required to place an order.',
            'items.*.product_id.required' => 'Each product line must specify a valid product.',
            'items.*.product_id.exists' => 'The selected product does not exist in inventory.',
            'items.*.quantity.required' => 'Quantity must be specified.',
            'items.*.quantity.min' => 'Quantity must be at least 1.',
        ];
    }
}
