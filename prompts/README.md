# Prompt Log — Store Order & Inventory Mini-System

This directory documents the prompt and development workflow used during the development of this project.

### Prompt Overview
- **User Request**: "analyze this document and complete this task and give me with good ui deisgn"
- **Attached Document**: *Mallow Technologies - Laravel Developer Mini Task — Take-Home Assignment*

### Key Requirements Implemented:
1. **Normalized Database Schema**:
   - `products`: id, name, code (unique), price_per_unit, tax_percentage, stock_on_hand, timestamps.
   - `customers`: id, name, email (unique), timestamps.
   - `orders`: id, order_number (unique), customer_id (foreign), subtotal, tax_amount, grand_total, status, timestamps.
   - `order_items`: id, order_id, product_id, product_name, product_code, quantity, unit_price, tax_percentage, line_subtotal, line_tax, line_total, timestamps.

2. **Concurrency Safety & Race Condition Prevention**:
   - Implemented pessimistic locking via `DB::transaction()` and `->lockForUpdate()`.
   - Primary key sorting (`ksort`) prevents deadlock risks across concurrent multiple-item orders.
   - Strict check: if stock is insufficient, an `InsufficientStockException` is thrown, rolling back the transaction with zero overselling.

3. **Asynchronous Queued Job**:
   - `SendOrderConfirmationEmail` queued via `DB::afterCommit(...)` so it only fires after database transactions are fully committed.

4. **API Endpoints**:
   - `POST /api/v1/orders` (Store order with stock deduction, tax computation, customer matching)
   - `GET /api/v1/customers/orders?email=...` (Customer order history)
   - `GET /api/v1/customers/search?email=...` (Auto-fill customer info)
   - `GET /api/v1/products` (Product catalog)
   - `GET /api/v1/products/low-stock?threshold=...` (Configurable low-stock items)

5. **POS Modern UI**:
   - Built with Tailwind CSS and Alpine.js.
   - Exact wireframe components: Customer Email/Name auto-fill, dynamic product rows, Low Stock Alert sidebar, payment calculator with cash return denomination breakdown, and printable invoice receipt modal.

6. **Automated Test Coverage**:
   - 8 automated tests passing (36 assertions) covering happy path, insufficient stock edge case, validation errors, history endpoint, low stock endpoint, and concurrent race-condition simulation.
