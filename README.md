# Store Order & Inventory Mini-System
### Mallow Technologies — Laravel Developer Take-Home Assignment

A retail counter order and inventory management system built with **Laravel 12**, **Tailwind CSS**, and **Alpine.js**. It records customer orders against a product catalog, maintains stock synchronization, prevents overselling under high concurrency, dispatches queued confirmation emails, and provides an intuitive Point-of-Sale (POS) interface based on the provided wireframe.

---

## 🚀 Features & Highlights

- **Normalized Database Schema**: Clean relations between `customers`, `products`, `orders`, and historical snapshot `order_items`.
- **Concurrency & Race Condition Safety (Zero Overselling)**:
  - Uses `DB::transaction()` paired with pessimistic database row locking (`->lockForUpdate()`).
  - Pre-sorts item keys (`ksort`) to eliminate deadlock hazards when multiple concurrent transactions lock multiple products.
  - Ensures that when two orders arrive nearly simultaneously for the last unit in stock, **exactly one succeeds** and the other fails cleanly with an `InsufficientStockException`.
- **Asynchronous Processing**:
  - `SendOrderConfirmationEmail` job dispatched via `DB::afterCommit()`. It simulates async email notifications without slowing down checkout response times.
- **RESTful API Endpoints**:
  - `POST /api/v1/orders`: Validates stock, calculates line totals & taxes, deducts stock, creates order & items, and dispatches email job.
  - `GET /api/v1/customers/orders?email={email}`: Retrieves complete purchase history for a customer.
  - `GET /api/v1/customers/search?email={email}`: Real-time customer lookup for POS auto-population.
  - `GET /api/v1/products`: Returns current catalog and live stock.
  - `GET /api/v1/products/low-stock?threshold={n}`: Returns products at or below a configurable threshold (defaults to `10` or `.env` `LOW_STOCK_THRESHOLD`).
- **Modern POS UI Design (Tailwind CSS + Alpine.js)**:
  - Implements the exact structure from the assignment wireframe:
    - Customer email with auto-fill customer name on blur/debounced typing.
    - Dynamic product lines with real-time tax and total calculations.
    - Sticky **Low Stock Alert** widget showing items nearing exhaustion.
    - Payment summary with cash tender calculation and change denomination breakdown (e.g., `20x1 + 2x1 + 1x1`).
    - Invoice Receipt modal with print support and confirmation notice.
    - Customer Order History tab & Live Inventory stock view.
- **Thorough Test Suite**:
  - Full PHPUnit test suite covering order creation, edge cases (insufficient stock), concurrency safety, validation errors, and API endpoints.

---

## 🛠️ Architecture & Design Decisions

### 1. Thin Controllers, Rich Services
- Controllers (`Api\OrderController`, `Api\CustomerController`, `Api\ProductController`) only handle HTTP transport, FormRequest validation, and JSON responses.
- Business logic is strictly encapsulated in `App\Services\OrderService`.

### 2. Concurrency Safety Mechanism
In high-throughput retail stores, race conditions can cause negative inventory if two customers purchase the last unit simultaneously. 
We solved this by:
```php
DB::transaction(function () use ($customerData, $items) {
    ksort($aggregatedItems); // Prevent deadlocks
    $products = Product::whereIn('id', array_keys($aggregatedItems))
        ->lockForUpdate() // Pessimistic row locking
        ->get();
    
    // Validate stock on locked record
    if ($product->stock_on_hand < $qty) {
        throw new InsufficientStockException(...);
    }
    
    $product->decrement('stock_on_hand', $qty);
    ...
});
```

### 3. Historical Order Snapshots
Prices and tax rates fluctuate. The `order_items` table preserves historical point-in-time product details (`product_name`, `product_code`, `unit_price`, `tax_percentage`, `line_subtotal`, `line_tax`, `line_total`) so subsequent catalog changes do not alter past receipts.

---

## 📦 Setup & Installation Instructions

### Prerequisites
- PHP >= 8.2 with SQLite or MySQL extension
- Composer
- Node.js & npm (optional, Tailwind & Alpine loaded via CDN)

### Step 1: Clone & Install Dependencies
```bash
git clone <your-repo-url>
cd mini-task
composer install
```

### Step 2: Environment Configuration
```bash
cp .env.example .env
php artisan key:generate
```

### Step 3: Database Migration & Seeding
```bash
php artisan migrate --seed
```
This runs migrations and seeds the database with initial products matching the wireframe (e.g. *Colgate Toothpaste*, *Parle-G*, *Bread*, *Milk*, *Eggs*) and sample customers.

### Step 4: Run the Development Server
```bash
php artisan serve
```
Visit **`http://127.0.0.1:8000`** in your browser to access the interactive POS interface.

### Step 5: (Optional) Run the Queue Worker
To process dispatched queued jobs asynchronously:
```bash
php artisan queue:work
```
*(By default, queued emails log directly to `storage/logs/laravel.log`).*

---

## 🧪 Running Automated Tests

Run the test suite using PHPUnit / Artisan:

```bash
php artisan test
```

### Tests Covered:
1. `ConcurrencyStockProtectionTest`: Verifies that if only 1 unit is left, one customer succeeds and concurrent orders fail with `InsufficientStockException`.
2. `OrderCreationTest > can_create_order_successfully_and_deduct_stock_and_dispatch_job`: Checks order total computation, stock deduction, and email job dispatching.
3. `OrderCreationTest > fails_cleanly_when_insufficient_stock`: Ensures no partial data is written on stock failure.
4. `OrderCreationTest > validates_required_fields`: Tests FormRequest validation.
5. `OrderCreationTest > customer_order_history_endpoint`: Tests history retrieval by customer email.
6. `OrderCreationTest > low_stock_products_endpoint`: Tests dynamic threshold filtering.

---

## 📑 API Reference

### 1. Create Order
- **Endpoint**: `POST /api/v1/orders`
- **Headers**: `Content-Type: application/json`, `Accept: application/json`
- **Request Body**:
```json
{
  "customer_name": "Thomas Mathew",
  "customer_email": "thomas@example.com",
  "items": [
    { "product_id": 1, "quantity": 2 },
    { "product_id": 2, "quantity": 5 }
  ]
}
```
- **Response (201 Created)**:
```json
{
  "success": true,
  "message": "Order created successfully.",
  "data": {
    "id": 1,
    "order_number": "ORD-ABCD-1234",
    "customer": { "name": "Thomas Mathew", "email": "thomas@example.com" },
    "subtotal": "150.00",
    "tax_amount": "12.00",
    "grand_total": "162.00",
    "items": [...]
  }
}
```

### 2. Customer Order History
- **Endpoint**: `GET /api/v1/customers/orders?email=thomas@example.com`

### 3. Configurable Low-Stock Items
- **Endpoint**: `GET /api/v1/products/low-stock?threshold=5`

---

## 📂 Project Structure

```
app/
├── Exceptions/
│   └── InsufficientStockException.php
├── Http/
│   ├── Controllers/Api/
│   │   ├── CustomerController.php
│   │   ├── OrderController.php
│   │   └── ProductController.php
│   └── Requests/
│       └── StoreOrderRequest.php
├── Jobs/
│   └── SendOrderConfirmationEmail.php
├── Models/
│   ├── Customer.php
│   ├── Order.php
│   ├── OrderItem.php
│   └── Product.php
└── Services/
    └── OrderService.php (Pessimistic locking & atomic order logic)
database/
├── factories/
│   ├── CustomerFactory.php
│   └── ProductFactory.php
├── migrations/
└── seeders/
resources/
└── views/
    └── pos.blade.php (Modern Tailwind POS UI)
prompts/
└── README.md (AI prompt log)
tests/
├── Feature/OrderCreationTest.php
└── Unit/ConcurrencyStockProtectionTest.php
```

---

## 💡 Assumptions & Ambiguity Resolutions

1. **Customer Existence**: If a customer does not exist in the database when placing an order, they are automatically created via `firstOrCreate`. If they exist, their name is updated if changed.
2. **Duplicate Items in Order Payload**: If an API client passes multiple line items with the same `product_id`, the `OrderService` aggregates their quantities before acquiring locks to prevent duplicate key race issues.
3. **Deadlock Prevention**: Product IDs are sorted in ascending numerical order (`ksort`) prior to acquiring locks so concurrent transactions lock resources in identical order.
4. **Tax Precision**: Taxes are rounded per line item (`round($subtotal * ($rate / 100), 2)`) to avoid fractional cent rounding mismatches on customer invoices.
