<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Store Billing & POS System</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#f0fdf4',
                            100: '#dcfce7',
                            500: '#22c55e',
                            600: '#16a34a',
                            700: '#15803d',
                            800: '#166534',
                        }
                    }
                }
            }
        }
    </script>
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.3/dist/cdn.min.js"></script>
    <!-- FontAwesome for clean modern icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        [x-cloak] { display: none !important; }
        @media print {
            body * { visibility: hidden; }
            #printable-receipt, #printable-receipt * { visibility: visible; }
            #printable-receipt { position: absolute; left: 0; top: 0; width: 100%; }
        }
    </style>
</head>
<body class="bg-slate-100 text-slate-800 antialiased min-h-screen flex flex-col font-sans" x-data="posApp()" x-init="initApp()" x-cloak>

    <!-- Top Navigation Header -->
    <header class="bg-slate-900 text-white shadow-lg border-b border-slate-800 sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-emerald-500 to-teal-400 flex items-center justify-center text-white shadow-md">
                    <i class="fa-solid fa-cash-register text-lg"></i>
                </div>
                <div>
                    <h1 class="text-lg font-bold tracking-tight">Store Billing <span class="text-emerald-400 font-semibold">— New Order</span></h1>
                    <p class="text-xs text-slate-400 font-medium">Mallow Tech Retail Counter POS System</p>
                </div>
            </div>

            <!-- Tab Switcher / Actions -->
            <div class="flex items-center space-x-3">
                <button @click="activeTab = 'pos'" :class="activeTab === 'pos' ? 'bg-emerald-600 text-white' : 'bg-slate-800 text-slate-300 hover:bg-slate-700'" class="px-4 py-2 rounded-lg text-sm font-semibold transition flex items-center gap-2 shadow-sm">
                    <i class="fa-solid fa-cart-shopping"></i> POS Counter
                </button>
                <button @click="activeTab = 'history'" :class="activeTab === 'history' ? 'bg-emerald-600 text-white' : 'bg-slate-800 text-slate-300 hover:bg-slate-700'" class="px-4 py-2 rounded-lg text-sm font-semibold transition flex items-center gap-2 shadow-sm">
                    <i class="fa-solid fa-clock-rotate-left"></i> Customer History
                </button>
                <button @click="activeTab = 'inventory'" :class="activeTab === 'inventory' ? 'bg-emerald-600 text-white' : 'bg-slate-800 text-slate-300 hover:bg-slate-700'" class="px-4 py-2 rounded-lg text-sm font-semibold transition flex items-center gap-2 shadow-sm">
                    <i class="fa-solid fa-boxes-stacked"></i> Catalog & Stock
                </button>
            </div>
        </div>
    </header>

    <!-- Notification Toast -->
    <div class="fixed top-20 right-5 z-50 flex flex-col gap-2 pointer-events-none" style="min-width: 320px;">
        <template x-for="(toast, index) in toasts" :key="index">
            <div x-transition class="pointer-events-auto p-4 rounded-xl shadow-xl flex items-start gap-3 border text-sm backdrop-blur-md"
                :class="{
                    'bg-emerald-900/90 border-emerald-600 text-emerald-100': toast.type === 'success',
                    'bg-rose-900/90 border-rose-600 text-rose-100': toast.type === 'error',
                    'bg-amber-900/90 border-amber-600 text-amber-100': toast.type === 'warning',
                    'bg-blue-900/90 border-blue-600 text-blue-100': toast.type === 'info'
                }">
                <i class="mt-0.5 text-base" :class="{
                    'fa-solid fa-circle-check text-emerald-400': toast.type === 'success',
                    'fa-solid fa-circle-xmark text-rose-400': toast.type === 'error',
                    'fa-solid fa-triangle-exclamation text-amber-400': toast.type === 'warning',
                    'fa-solid fa-info-circle text-blue-400': toast.type === 'info'
                }"></i>
                <div class="flex-1">
                    <p class="font-semibold" x-text="toast.title"></p>
                    <p class="text-xs opacity-90" x-text="toast.message"></p>
                </div>
                <button @click="removeToast(index)" class="opacity-70 hover:opacity-100 text-xs"><i class="fa-solid fa-xmark"></i></button>
            </div>
        </template>
    </div>

    <!-- MAIN BODY CONTENT -->
    <main class="flex-1 max-w-7xl w-full mx-auto p-4 sm:p-6 lg:p-8">
        
        <!-- POS BILLING TAB (Wireframe exact implementation + modern polish) -->
        <div x-show="activeTab === 'pos'" class="space-y-6">
            
            <!-- Customer Section Header -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 p-6">
                <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-100">
                    <h2 class="text-base font-bold text-slate-800 flex items-center gap-2">
                        <i class="fa-solid fa-user-tag text-emerald-600"></i> Customer Details
                    </h2>
                    <span class="text-xs px-2.5 py-1 rounded-full bg-slate-100 text-slate-600 font-medium">
                        Existing customers auto-populate
                    </span>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                            Customer Email <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <i class="fa-regular fa-envelope"></i>
                            </div>
                            <input type="email" x-model="customer.email" @input.debounce.400ms="searchCustomer()"
                                placeholder="e.g. thomas@example.com"
                                class="w-full pl-10 pr-10 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition">
                            <div class="absolute inset-y-0 right-0 pr-3.5 flex items-center" x-show="isSearchingCustomer">
                                <i class="fa-solid fa-spinner fa-spin text-emerald-600 text-sm"></i>
                            </div>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                            Customer Name <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <i class="fa-regular fa-user"></i>
                            </div>
                            <input type="text" x-model="customer.name"
                                placeholder="auto-filled if email exists or enter new"
                                class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Middle Section: Products Grid + Low Stock Alert Sidebar -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                <!-- Products Table Section (2 Cols wide on large screen) -->
                <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-slate-200/80 p-6 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-100">
                            <h2 class="text-base font-bold text-slate-800 flex items-center gap-2">
                                <i class="fa-solid fa-basket-shopping text-emerald-600"></i> Order Items
                            </h2>
                            <button @click="addProductLine()" type="button" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold bg-emerald-50 text-emerald-700 hover:bg-emerald-100 rounded-lg transition border border-emerald-200">
                                <i class="fa-solid fa-plus"></i> Add Product
                            </button>
                        </div>

                        <!-- Product Table -->
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-sm">
                                <thead>
                                    <tr class="text-xs uppercase tracking-wider text-slate-500 bg-slate-50/70 border-b border-slate-200">
                                        <th class="py-3 px-3 rounded-l-lg font-bold">Product</th>
                                        <th class="py-3 px-3 font-bold w-24 text-center">Qty</th>
                                        <th class="py-3 px-3 font-bold w-28 text-right">Price</th>
                                        <th class="py-3 px-3 font-bold w-28 text-right">Line Total</th>
                                        <th class="py-3 px-2 rounded-r-lg font-bold w-12 text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    <template x-for="(line, index) in orderLines" :key="line.key">
                                        <tr class="hover:bg-slate-50/80 transition-colors">
                                            <!-- Product Select -->
                                            <td class="py-3 px-3">
                                                <select x-model="line.product_id" @change="onProductSelect(line)"
                                                    class="w-full py-2 px-2.5 text-xs bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500 font-medium">
                                                    <option value="">+ select product from catalog...</option>
                                                    <template x-for="p in catalog" :key="p.id">
                                                        <option :value="p.id" :disabled="p.stock_on_hand <= 0"
                                                            x-text="p.name + ' (' + p.code + ') - ₹' + p.price_per_unit + ' [Stock: ' + p.stock_on_hand + ']'">
                                                        </option>
                                                    </template>
                                                </select>
                                                <!-- Small tax and stock note under product line -->
                                                <div class="mt-1 text-[11px] text-slate-500 flex gap-2" x-show="line.product">
                                                    <span>Tax: <strong class="text-slate-700" x-text="line.product?.tax_percentage + '%'"></strong></span>
                                                    <span>| Stock Available: <strong :class="line.product?.stock_on_hand < 5 ? 'text-amber-600 font-bold' : 'text-slate-700'" x-text="line.product?.stock_on_hand"></strong></span>
                                                </div>
                                            </td>

                                            <!-- Quantity -->
                                            <td class="py-3 px-3">
                                                <div class="flex items-center justify-center space-x-1">
                                                    <input type="number" min="1" :max="line.product ? line.product.stock_on_hand : 999"
                                                        x-model.number="line.quantity" @input="calculateTotals()"
                                                        class="w-16 py-1.5 px-2 text-center text-xs font-semibold bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500">
                                                </div>
                                            </td>

                                            <!-- Price per unit -->
                                            <td class="py-3 px-3 text-right font-medium text-slate-700 text-xs">
                                                <span x-text="line.product ? '₹' + parseFloat(line.product.price_per_unit).toFixed(2) : '—'"></span>
                                            </td>

                                            <!-- Computed Line Total -->
                                            <td class="py-3 px-3 text-right font-bold text-slate-900 text-xs">
                                                <span x-text="line.product ? '₹' + getLineTotal(line).toFixed(2) : '₹0.00'"></span>
                                            </td>

                                            <!-- Remove Action -->
                                            <td class="py-3 px-2 text-center">
                                                <button @click="removeProductLine(index)" type="button" class="w-8 h-8 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition flex items-center justify-center">
                                                    <i class="fa-regular fa-trash-can text-sm"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    </template>
                                    <tr x-show="orderLines.length === 0">
                                        <td colspan="5" class="text-center py-8 text-slate-400 text-xs">
                                            No products added yet. Click <strong>+ Add Product</strong> or pick from catalog below.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Bottom table button -->
                    <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between">
                        <button @click="addProductLine()" type="button" class="inline-flex items-center gap-2 px-4 py-2 bg-slate-800 text-white rounded-xl text-xs font-bold hover:bg-slate-700 transition shadow-sm">
                            <i class="fa-solid fa-plus"></i> Add Product Row
                        </button>
                        <span class="text-xs text-slate-500 font-medium" x-text="orderLines.filter(l => l.product_id).length + ' product(s) selected'"></span>
                    </div>
                </div>

                <!-- Low Stock Alert Box (Wireframe exact component) -->
                <div class="bg-amber-50/70 border-2 border-amber-300/80 rounded-2xl p-5 shadow-sm flex flex-col">
                    <div class="flex items-center justify-between mb-3 pb-2 border-b border-amber-200">
                        <h3 class="text-sm font-bold text-amber-900 flex items-center gap-2">
                            <i class="fa-solid fa-triangle-exclamation text-amber-600 animate-pulse"></i> Low Stock Alert
                        </h3>
                        <span class="text-[11px] font-semibold bg-amber-200 text-amber-900 px-2 py-0.5 rounded-full" x-text="'Threshold: ≤' + lowStockThreshold"></span>
                    </div>
                    
                    <p class="text-xs text-amber-800/80 mb-3">
                        Products nearing depletion. Reorder promptly to avoid stockouts.
                    </p>

                    <div class="flex-1 overflow-y-auto max-h-80 space-y-2 pr-1">
                        <template x-for="item in lowStockProducts" :key="item.id">
                            <div class="p-2.5 bg-white/90 rounded-xl border border-amber-200 flex items-center justify-between shadow-xs hover:border-amber-400 transition">
                                <div>
                                    <p class="text-xs font-bold text-slate-800" x-text="item.name"></p>
                                    <p class="text-[10px] text-slate-500 font-mono" x-text="item.code"></p>
                                </div>
                                <div class="text-right">
                                    <span class="inline-block px-2 py-0.5 rounded-md text-xs font-black"
                                        :class="item.stock_on_hand <= 2 ? 'bg-rose-100 text-rose-700' : 'bg-amber-100 text-amber-800'"
                                        x-text="item.stock_on_hand + ' units left'">
                                    </span>
                                </div>
                            </div>
                        </template>

                        <div x-show="lowStockProducts.length === 0" class="text-center py-6 text-amber-700/60 text-xs">
                            <i class="fa-solid fa-circle-check text-2xl text-emerald-500 mb-1 block"></i>
                            All inventory levels are above threshold!
                        </div>
                    </div>

                    <div class="mt-4 pt-3 border-t border-amber-200 text-center">
                        <button @click="fetchLowStock()" class="text-xs font-bold text-amber-800 hover:text-amber-950 flex items-center justify-center gap-1.5 w-full">
                            <i class="fa-solid fa-arrows-rotate"></i> Refresh Stock Watch
                        </button>
                    </div>
                </div>
            </div>

            <!-- Payment & Checkout Section (Wireframe exact implementation) -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-start">
                    
                    <!-- Left: Subtotal, Tax, Grand Total calculation summary -->
                    <div class="space-y-3.5 bg-slate-50/80 p-5 rounded-2xl border border-slate-200/80">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500 pb-2 border-b border-slate-200">Payment Breakdown</h3>
                        
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-slate-600 font-medium">Subtotal</span>
                            <span class="font-bold text-slate-800" x-text="'₹' + summary.subtotal.toFixed(2)">₹0.00</span>
                        </div>
                        
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-slate-600 font-medium">Tax</span>
                            <span class="font-bold text-slate-800" x-text="'₹' + summary.tax.toFixed(2)">₹0.00</span>
                        </div>
                        
                        <div class="pt-3 border-t-2 border-slate-300 flex justify-between items-center text-base">
                            <span class="font-extrabold text-slate-900">Grand Total</span>
                            <span class="text-xl font-black text-emerald-600 font-mono" x-text="'₹' + summary.grandTotal.toFixed(2)">₹0.00</span>
                        </div>

                        <!-- Tender Cash & Return Calculation (From Wireframe) -->
                        <div class="pt-4 border-t border-slate-200 space-y-3">
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">
                                    Amount Given by Customer
                                </label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none font-bold text-slate-500">₹</span>
                                    <input type="number" step="any" x-model.number="amountTendered"
                                        placeholder="Enter cash given by customer..."
                                        class="w-full pl-8 pr-4 py-2.5 bg-white border border-slate-300 rounded-xl text-sm font-bold text-slate-900 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                                </div>
                            </div>

                            <div class="p-3 bg-white rounded-xl border border-slate-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1">
                                <span class="text-xs font-bold uppercase tracking-wider text-slate-600">Balance to Return:</span>
                                <div class="text-right">
                                    <span class="text-sm font-black font-mono"
                                        :class="changeDue >= 0 ? 'text-emerald-700' : 'text-rose-600'"
                                        x-text="'₹' + (changeDue > 0 ? changeDue.toFixed(2) : '0.00')">
                                    </span>
                                    <div class="text-[11px] text-slate-500 font-mono" x-show="changeBreakdown" x-text="changeBreakdown"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right: Big Action Button -->
                    <div class="flex flex-col justify-center h-full space-y-4">
                        <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-2xl text-xs text-emerald-800 space-y-1">
                            <p class="font-bold flex items-center gap-1.5">
                                <i class="fa-solid fa-bolt text-emerald-600"></i> Atomic & Concurrency-Safe
                            </p>
                            <p>Orders use pessimistic row locks (<code class="bg-emerald-100 px-1 rounded">lockForUpdate</code>) to guarantee zero overselling. An asynchronous email job is queued automatically upon creation.</p>
                        </div>

                        <button @click="generateBill()" :disabled="isSubmitting || orderLines.length === 0"
                            class="w-full py-4 bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 disabled:cursor-not-allowed text-white rounded-2xl font-black text-lg shadow-lg hover:shadow-xl transition-all duration-200 flex items-center justify-center gap-3">
                            <span x-show="!isSubmitting" class="flex items-center gap-2">
                                <i class="fa-solid fa-file-invoice-dollar text-xl"></i> Generate Bill
                            </span>
                            <span x-show="isSubmitting" class="flex items-center gap-2">
                                <i class="fa-solid fa-spinner fa-spin text-xl"></i> Processing Order...
                            </span>
                        </button>
                        <p class="text-center text-xs text-slate-500 font-medium">
                            <i class="fa-solid fa-receipt mr-1 text-slate-400"></i> Shows bill on screen & triggers queued confirmation email
                        </p>
                    </div>

                </div>
            </div>

        </div>

        <!-- CUSTOMER ORDER HISTORY TAB -->
        <div x-show="activeTab === 'history'" class="space-y-6">
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 p-6">
                <div class="max-w-2xl mx-auto space-y-4">
                    <div class="text-center">
                        <div class="w-12 h-12 bg-emerald-100 text-emerald-600 rounded-2xl mx-auto flex items-center justify-center text-xl mb-2">
                            <i class="fa-solid fa-clock-rotate-left"></i>
                        </div>
                        <h2 class="text-xl font-bold text-slate-900">Customer Order History</h2>
                        <p class="text-xs text-slate-500">Search any customer's previous orders and purchases by email address</p>
                    </div>

                    <div class="flex gap-2">
                        <div class="relative flex-1">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <i class="fa-regular fa-envelope"></i>
                            </div>
                            <input type="email" x-model="historyEmail" @keydown.enter="fetchCustomerOrders()"
                                placeholder="Enter customer email (e.g. thomas@example.com)..."
                                class="w-full pl-10 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition">
                        </div>
                        <button @click="fetchCustomerOrders()" :disabled="isLoadingHistory"
                            class="px-6 py-3 bg-slate-900 text-white rounded-xl text-sm font-bold hover:bg-slate-800 transition flex items-center gap-2">
                            <i class="fa-solid fa-magnifying-glass" x-show="!isLoadingHistory"></i>
                            <i class="fa-solid fa-spinner fa-spin" x-show="isLoadingHistory"></i>
                            Search
                        </button>
                    </div>
                </div>

                <!-- Customer Details card if found -->
                <div x-show="historyCustomer" class="mt-6 p-4 bg-emerald-50/70 border border-emerald-200 rounded-2xl flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-emerald-600 text-white flex items-center justify-center font-bold text-sm" x-text="historyCustomer?.name?.charAt(0) || 'C'"></div>
                        <div>
                            <p class="text-sm font-bold text-slate-900" x-text="historyCustomer?.name"></p>
                            <p class="text-xs text-slate-600 font-mono" x-text="historyCustomer?.email"></p>
                        </div>
                    </div>
                    <span class="px-3 py-1 bg-white rounded-full text-xs font-bold text-emerald-800 shadow-xs" x-text="customerOrders.length + ' Orders Found'"></span>
                </div>

                <!-- Orders List -->
                <div class="mt-6 space-y-4">
                    <template x-for="ord in customerOrders" :key="ord.id">
                        <div class="p-5 bg-white border border-slate-200/90 rounded-2xl shadow-xs hover:border-slate-300 transition">
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between pb-3 border-b border-slate-100 gap-2">
                                <div>
                                    <span class="text-xs font-mono font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-md" x-text="ord.order_number"></span>
                                    <span class="text-xs text-slate-400 ml-2" x-text="new Date(ord.created_at).toLocaleString()"></span>
                                </div>
                                <div class="text-right">
                                    <span class="text-xs text-slate-500 mr-2">Total Paid:</span>
                                    <span class="text-base font-black text-slate-900" x-text="'₹' + parseFloat(ord.grand_total).toFixed(2)"></span>
                                </div>
                            </div>

                            <!-- Items -->
                            <div class="mt-3 overflow-x-auto">
                                <table class="w-full text-xs text-left">
                                    <thead>
                                        <tr class="text-slate-400 border-b border-slate-100">
                                            <th class="py-1">Item</th>
                                            <th class="py-1 text-center">Qty</th>
                                            <th class="py-1 text-right">Unit Price</th>
                                            <th class="py-1 text-right">Tax</th>
                                            <th class="py-1 text-right">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-50 text-slate-700 font-medium">
                                        <template x-for="it in ord.items" :key="it.id">
                                            <tr>
                                                <td class="py-2" x-text="it.product_name + ' (' + it.product_code + ')'"></td>
                                                <td class="py-2 text-center font-bold" x-text="it.quantity"></td>
                                                <td class="py-2 text-right" x-text="'₹' + parseFloat(it.unit_price).toFixed(2)"></td>
                                                <td class="py-2 text-right" x-text="'₹' + parseFloat(it.line_tax).toFixed(2) + ' (' + it.tax_percentage + '%)'"></td>
                                                <td class="py-2 text-right font-bold text-slate-900" x-text="'₹' + parseFloat(it.line_total).toFixed(2)"></td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </template>

                    <div x-show="customerOrders.length === 0 && hasSearchedHistory" class="text-center py-12 text-slate-400">
                        <i class="fa-regular fa-folder-open text-3xl mb-2 block"></i>
                        No orders recorded yet for this customer.
                    </div>
                </div>
            </div>
        </div>

        <!-- CATALOG & STOCK MANAGEMENT TAB -->
        <div x-show="activeTab === 'inventory'" class="space-y-6">
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 p-6">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between pb-4 border-b border-slate-100 gap-3">
                    <div>
                        <h2 class="text-base font-bold text-slate-800 flex items-center gap-2">
                            <i class="fa-solid fa-cubes-stacked text-emerald-600"></i> Live Product Catalog
                        </h2>
                        <p class="text-xs text-slate-500">Real-time inventory levels, pricing, and GST tax percentages</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="relative">
                            <input type="text" x-model="searchCatalogQuery" placeholder="Filter products..."
                                class="pl-8 pr-3 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-2 focus:ring-emerald-500">
                            <i class="fa-solid fa-magnifying-glass absolute left-2.5 top-2.5 text-slate-400 text-xs"></i>
                        </div>
                        <button @click="fetchCatalog()" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 rounded-lg text-xs font-bold text-slate-700 transition">
                            <i class="fa-solid fa-arrows-rotate mr-1"></i> Refresh
                        </button>
                    </div>
                </div>

                <div class="mt-4 overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="text-xs uppercase tracking-wider text-slate-500 bg-slate-50/70 border-b border-slate-200">
                                <th class="py-3 px-3 font-bold">Code</th>
                                <th class="py-3 px-3 font-bold">Product Name</th>
                                <th class="py-3 px-3 font-bold text-right">Price / Unit</th>
                                <th class="py-3 px-3 font-bold text-right">Tax Rate</th>
                                <th class="py-3 px-3 font-bold text-center">Stock on Hand</th>
                                <th class="py-3 px-3 font-bold text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <template x-for="p in filteredCatalog" :key="p.id">
                                <tr class="hover:bg-slate-50/60">
                                    <td class="py-3 px-3 font-mono font-bold text-xs text-slate-600" x-text="p.code"></td>
                                    <td class="py-3 px-3 font-semibold text-slate-800" x-text="p.name"></td>
                                    <td class="py-3 px-3 text-right font-medium text-slate-700" x-text="'₹' + parseFloat(p.price_per_unit).toFixed(2)"></td>
                                    <td class="py-3 px-3 text-right font-medium text-slate-700" x-text="parseFloat(p.tax_percentage).toFixed(2) + '%'"></td>
                                    <td class="py-3 px-3 text-center">
                                        <span class="inline-block px-2.5 py-1 rounded-lg text-xs font-black font-mono"
                                            :class="{
                                                'bg-rose-100 text-rose-700': p.stock_on_hand <= 3,
                                                'bg-amber-100 text-amber-800': p.stock_on_hand > 3 && p.stock_on_hand <= 10,
                                                'bg-emerald-100 text-emerald-800': p.stock_on_hand > 10
                                            }"
                                            x-text="p.stock_on_hand">
                                        </span>
                                    </td>
                                    <td class="py-3 px-3 text-center">
                                        <span x-show="p.stock_on_hand > 10" class="text-[11px] font-semibold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full">In Stock</span>
                                        <span x-show="p.stock_on_hand <= 10 && p.stock_on_hand > 0" class="text-[11px] font-semibold text-amber-700 bg-amber-50 px-2 py-0.5 rounded-full">Low Stock</span>
                                        <span x-show="p.stock_on_hand <= 0" class="text-[11px] font-semibold text-rose-700 bg-rose-50 px-2 py-0.5 rounded-full">Out of Stock</span>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </main>

    <!-- INVOICE RECEIPT MODAL -->
    <div x-show="completedOrder" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs" x-transition>
        <div @click.away="completedOrder = null" class="bg-white rounded-3xl shadow-2xl max-w-lg w-full overflow-hidden border border-slate-200">
            <!-- Modal Header -->
            <div class="bg-gradient-to-r from-emerald-600 to-teal-600 px-6 py-5 text-white flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-black tracking-tight flex items-center gap-2">
                        <i class="fa-solid fa-circle-check text-emerald-200"></i> Bill Generated Successfully!
                    </h3>
                    <p class="text-xs text-emerald-100 font-medium" x-text="'Order #' + completedOrder?.order_number"></p>
                </div>
                <button @click="completedOrder = null" class="w-8 h-8 rounded-full bg-white/20 hover:bg-white/30 flex items-center justify-center text-white transition">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <!-- Receipt Content (Printable) -->
            <div id="printable-receipt" class="p-6 space-y-5 text-slate-800">
                <div class="text-center pb-4 border-b border-slate-200">
                    <h4 class="font-extrabold text-base tracking-wide uppercase text-slate-900">Mallow Technologies Retail</h4>
                    <p class="text-xs text-slate-500">Retail Counter Order Receipt</p>
                    <p class="text-[11px] text-slate-400 font-mono mt-1" x-text="new Date(completedOrder?.created_at).toLocaleString()"></p>
                </div>

                <!-- Customer Info -->
                <div class="grid grid-cols-2 text-xs gap-2 bg-slate-50 p-3 rounded-xl border border-slate-100">
                    <div>
                        <span class="text-slate-400 block uppercase font-bold text-[10px]">Customer Name</span>
                        <span class="font-bold text-slate-800" x-text="completedOrder?.customer?.name"></span>
                    </div>
                    <div>
                        <span class="text-slate-400 block uppercase font-bold text-[10px]">Customer Email</span>
                        <span class="font-bold text-slate-800 font-mono text-[11px]" x-text="completedOrder?.customer?.email"></span>
                    </div>
                </div>

                <!-- Line Items -->
                <div class="space-y-2">
                    <div class="text-xs font-bold uppercase tracking-wider text-slate-400 border-b pb-1">Items Purchased</div>
                    <template x-for="item in completedOrder?.items" :key="item.id">
                        <div class="flex justify-between items-center text-xs py-1">
                            <div>
                                <span class="font-semibold text-slate-800" x-text="item.product_name"></span>
                                <span class="text-slate-400 block text-[10px]" x-text="item.quantity + ' x ₹' + parseFloat(item.unit_price).toFixed(2) + ' (Tax: ' + item.tax_percentage + '%)'"></span>
                            </div>
                            <span class="font-bold text-slate-900" x-text="'₹' + parseFloat(item.line_total).toFixed(2)"></span>
                        </div>
                    </template>
                </div>

                <!-- Totals -->
                <div class="pt-3 border-t border-slate-200 space-y-1.5 text-xs">
                    <div class="flex justify-between text-slate-600">
                        <span>Subtotal:</span>
                        <span class="font-bold" x-text="'₹' + parseFloat(completedOrder?.subtotal).toFixed(2)"></span>
                    </div>
                    <div class="flex justify-between text-slate-600">
                        <span>Tax Amount:</span>
                        <span class="font-bold" x-text="'₹' + parseFloat(completedOrder?.tax_amount).toFixed(2)"></span>
                    </div>
                    <div class="flex justify-between text-base font-black text-slate-900 pt-2 border-t border-slate-200">
                        <span>Grand Total:</span>
                        <span class="text-emerald-700 font-mono" x-text="'₹' + parseFloat(completedOrder?.grand_total).toFixed(2)"></span>
                    </div>
                    
                    <div x-show="lastPaymentTendered > 0" class="pt-2 text-xs border-t border-dashed border-slate-200 space-y-1">
                        <div class="flex justify-between text-slate-600">
                            <span>Amount Tendered:</span>
                            <span class="font-bold" x-text="'₹' + parseFloat(lastPaymentTendered).toFixed(2)"></span>
                        </div>
                        <div class="flex justify-between text-emerald-700 font-bold">
                            <span>Change Returned:</span>
                            <span x-text="'₹' + (lastPaymentTendered - parseFloat(completedOrder?.grand_total)).toFixed(2)"></span>
                        </div>
                    </div>
                </div>

                <!-- Email Simulated Notice -->
                <div class="p-3 bg-blue-50 border border-blue-200 rounded-xl text-[11px] text-blue-800 flex items-center gap-2">
                    <i class="fa-solid fa-envelope-circle-check text-blue-600 text-sm"></i>
                    <span>Queued confirmation email dispatched via <strong>SendOrderConfirmationEmail</strong> job!</span>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="px-6 py-4 bg-slate-50 border-t border-slate-200 flex justify-between items-center">
                <button @click="window.print()" class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-xl text-xs font-bold transition flex items-center gap-1.5">
                    <i class="fa-solid fa-print"></i> Print Receipt
                </button>
                <button @click="resetFormAndCloseModal()" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition">
                    New Order
                </button>
            </div>
        </div>
    </div>


    <script>
        function posApp() {
            return {
                activeTab: 'pos',
                toasts: [],
                catalog: [],
                lowStockProducts: [],
                lowStockThreshold: 10,
                searchCatalogQuery: '',

                customer: {
                    name: '',
                    email: ''
                },
                isSearchingCustomer: false,

                orderLines: [
                    { key: Date.now(), product_id: '', product: null, quantity: 1 }
                ],
                amountTendered: '',
                isSubmitting: false,
                completedOrder: null,
                lastPaymentTendered: 0,

                // History Tab
                historyEmail: '',
                isLoadingHistory: false,
                hasSearchedHistory: false,
                historyCustomer: null,
                customerOrders: [],

                summary: {
                    subtotal: 0,
                    tax: 0,
                    grandTotal: 0
                },

                get changeDue() {
                    const tendered = parseFloat(this.amountTendered) || 0;
                    return tendered - this.summary.grandTotal;
                },

                get changeBreakdown() {
                    let due = Math.round(this.changeDue);
                    if (due <= 0) return '';
                    
                    const denoms = [500, 200, 100, 50, 20, 10, 5, 2, 1];
                    const parts = [];
                    for (const d of denoms) {
                        if (due >= d) {
                            const count = Math.floor(due / d);
                            parts.push(`${count}x${d}`);
                            due %= d;
                        }
                    }
                    return parts.length ? 'Denominations: ' + parts.join(' + ') : '';
                },

                get filteredCatalog() {
                    if (!this.searchCatalogQuery.trim()) return this.catalog;
                    const q = this.searchCatalogQuery.toLowerCase();
                    return this.catalog.filter(p => 
                        p.name.toLowerCase().includes(q) || 
                        p.code.toLowerCase().includes(q)
                    );
                },

                async initApp() {
                    await Promise.all([
                        this.fetchCatalog(),
                        this.fetchLowStock()
                    ]);
                },

                showToast(title, message, type = 'info') {
                    const id = Date.now();
                    this.toasts.push({ id, title, message, type });
                    setTimeout(() => {
                        this.removeToast(this.toasts.findIndex(t => t.id === id));
                    }, 4500);
                },

                removeToast(idx) {
                    if (idx > -1) this.toasts.splice(idx, 1);
                },

                async fetchCatalog() {
                    try {
                        const res = await fetch('/api/v1/products');
                        const data = await res.json();
                        if (data.success) {
                            this.catalog = data.data;
                        }
                    } catch (e) {
                        console.error('Failed to load products', e);
                    }
                },

                async fetchLowStock() {
                    try {
                        const res = await fetch('/api/v1/products/low-stock');
                        const data = await res.json();
                        if (data.success) {
                            this.lowStockProducts = data.data;
                            this.lowStockThreshold = data.threshold;
                        }
                    } catch (e) {
                        console.error('Failed to load low-stock', e);
                    }
                },

                async searchCustomer() {
                    if (!this.customer.email || !this.customer.email.includes('@')) return;
                    this.isSearchingCustomer = true;
                    try {
                        const res = await fetch(`/api/v1/customers/search?email=${encodeURIComponent(this.customer.email)}`);
                        const data = await res.json();
                        if (data.found && data.customer) {
                            this.customer.name = data.customer.name;
                            this.showToast('Customer Found', `Welcome back, ${data.customer.name}!`, 'info');
                        }
                    } catch (e) {
                        console.error('Customer lookup error', e);
                    } finally {
                        this.isSearchingCustomer = false;
                    }
                },

                addProductLine() {
                    this.orderLines.push({
                        key: Date.now() + Math.random(),
                        product_id: '',
                        product: null,
                        quantity: 1
                    });
                },

                removeProductLine(idx) {
                    this.orderLines.splice(idx, 1);
                    this.calculateTotals();
                },

                onProductSelect(line) {
                    const prod = this.catalog.find(p => p.id == line.product_id);
                    line.product = prod || null;
                    if (prod && prod.stock_on_hand < line.quantity) {
                        line.quantity = Math.max(1, prod.stock_on_hand);
                    }
                    this.calculateTotals();
                },

                getLineTotal(line) {
                    if (!line.product) return 0;
                    const unitPrice = parseFloat(line.product.price_per_unit);
                    const qty = parseInt(line.quantity) || 0;
                    const taxRate = parseFloat(line.product.tax_percentage) || 0;
                    const sub = unitPrice * qty;
                    const tax = sub * (taxRate / 100);
                    return sub + tax;
                },

                calculateTotals() {
                    let sub = 0;
                    let tax = 0;

                    for (const line of this.orderLines) {
                        if (!line.product) continue;
                        const qty = parseInt(line.quantity) || 0;
                        const unitPrice = parseFloat(line.product.price_per_unit) || 0;
                        const taxRate = parseFloat(line.product.tax_percentage) || 0;

                        const lineSub = unitPrice * qty;
                        const lineTax = lineSub * (taxRate / 100);

                        sub += lineSub;
                        tax += lineTax;
                    }

                    this.summary.subtotal = Math.round(sub * 100) / 100;
                    this.summary.tax = Math.round(tax * 100) / 100;
                    this.summary.grandTotal = Math.round((sub + tax) * 100) / 100;
                },

                async generateBill() {
                    if (!this.customer.email.trim() || !this.customer.name.trim()) {
                        this.showToast('Validation Error', 'Customer Name and Email are both required.', 'error');
                        return;
                    }

                    const validLines = this.orderLines.filter(l => l.product_id && l.quantity > 0);
                    if (validLines.length === 0) {
                        this.showToast('Validation Error', 'Please select at least one product with quantity ≥ 1.', 'error');
                        return;
                    }

                    // Pre-check stock on client side for immediate feedback
                    for (const line of validLines) {
                        if (line.product && line.quantity > line.product.stock_on_hand) {
                            this.showToast(
                                'Stock Exceeded',
                                `Only ${line.product.stock_on_hand} units available for ${line.product.name}.`,
                                'error'
                            );
                            return;
                        }
                    }

                    this.isSubmitting = true;
                    try {
                        const payload = {
                            customer_email: this.customer.email.trim(),
                            customer_name: this.customer.name.trim(),
                            items: validLines.map(l => ({
                                product_id: parseInt(l.product_id),
                                quantity: parseInt(l.quantity)
                            }))
                        };

                        const res = await fetch('/api/v1/orders', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify(payload)
                        });

                        const result = await res.json();

                        if (res.ok && result.success) {
                            this.completedOrder = result.data;
                            this.lastPaymentTendered = parseFloat(this.amountTendered) || 0;
                            this.showToast('Order Placed!', `Order #${result.data.order_number} recorded successfully.`, 'success');

                            // Refresh Catalog and Low stock in background
                            await Promise.all([
                                this.fetchCatalog(),
                                this.fetchLowStock()
                            ]);
                        } else {
                            this.showToast('Order Failed', result.message || 'Unable to place order.', 'error');
                        }
                    } catch (err) {
                        this.showToast('Error', 'Network or server communication error.', 'error');
                        console.error(err);
                    } finally {
                        this.isSubmitting = false;
                    }
                },

                resetFormAndCloseModal() {
                    this.completedOrder = null;
                    this.orderLines = [
                        { key: Date.now(), product_id: '', product: null, quantity: 1 }
                    ];
                    this.amountTendered = '';
                    this.calculateTotals();
                },

                async fetchCustomerOrders() {
                    if (!this.historyEmail.trim()) {
                        this.showToast('Input Required', 'Please enter a customer email address.', 'warning');
                        return;
                    }

                    this.isLoadingHistory = true;
                    this.hasSearchedHistory = true;
                    try {
                        const res = await fetch(`/api/v1/customers/orders?email=${encodeURIComponent(this.historyEmail.trim())}`);
                        const data = await res.json();

                        if (data.found) {
                            this.historyCustomer = data.customer;
                            this.customerOrders = data.orders;
                        } else {
                            this.historyCustomer = null;
                            this.customerOrders = [];
                            this.showToast('Not Found', 'No records found for this email address.', 'info');
                        }
                    } catch (e) {
                        this.showToast('Error', 'Failed to retrieve order history.', 'error');
                    } finally {
                        this.isLoadingHistory = false;
                    }
                }
            }
        }
    </script>
</body>
</html>
