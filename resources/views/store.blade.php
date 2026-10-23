
<x-layout title="Mystic Item Store">
    <main class="min-h-screen bg-slate-950 px-6 py-10 text-white">
        <div class="mx-auto max-w-7xl relative">

            <header class="mb-10">
                <p class="mb-2 text-sm uppercase tracking-[0.3em] text-amber-400">
                    Beyond the Ordinary
                </p>

                <h1 class="text-4xl font-bold md:text-5xl">
                    Mystic Item Store
                </h1>

                <p class="mt-3 text-slate-400">
                    Discover mystical artifacts and extraordinary items.
                </p>
            </header>

            <div
                id="order-message"
                class="fixed top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 z-50 mb-6 hidden rounded-lg border p-4"
                role="status"
                aria-live="polite"
            ></div>

            <form id="order-form">
                @csrf

                <section class="mb-8 grid gap-5 rounded-xl border border-slate-800 bg-slate-900 p-5 md:grid-cols-2">
                    <div>
                        <label for="customer_name" class="mb-2 block text-sm text-slate-300">
                            Your name
                        </label>

                        <input
                            id="customer_name"
                            name="customer_name"
                            type="text"
                            maxlength="255"
                            required
                            class="w-full rounded-lg border border-slate-700 bg-slate-950 p-3 text-white outline-none focus:border-amber-400"
                            placeholder="Enter your name"
                        >
                    </div>

                    <div>
                        <label for="customer_email" class="mb-2 block text-sm text-slate-300">
                            Your email
                        </label>

                        <input
                            id="customer_email"
                            name="customer_email"
                            type="email"
                            maxlength="255"
                            required
                            class="w-full rounded-lg border border-slate-700 bg-slate-950 p-3 text-white outline-none focus:border-amber-400"
                            placeholder="you@example.com"
                        >
                    </div>
                </section>

                <div class="mb-5 flex items-center justify-between">
                    <h2 class="text-2xl font-semibold">Available Items</h2>

                    <p class="text-sm text-slate-400">
                        {{ $products->count() }} items
                    </p>
                </div>

                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                    @forelse ($products as $product)
                        <article class="flex flex-col overflow-hidden rounded-xl border border-slate-800 bg-slate-900 transition hover:border-amber-500/60">

                            <div class="flex aspect-square items-center justify-center bg-slate-800">
                                @if ($product->image_path)
                                    <img
                                        src="{{ $product->image_path }}"
                                        alt="{{ $product->name }}"
                                        loading="lazy"
                                        class="h-full w-full object-cover"
                                    >
                                @else
                                    <span class="text-7xl text-amber-400" aria-hidden="true">
                                        ✧
                                    </span>
                                @endif
                            </div>

                            <div class="flex flex-1 flex-col p-5">
                                <p class="mb-2 text-xs uppercase tracking-wider text-amber-400">
                                    {{ $product->category_name ?? 'Unclassified' }}
                                </p>

                                <h3 class="text-xl font-semibold">
                                    {{ $product->name }}
                                </h3>

                                <p class="mt-2 flex-1 text-sm leading-relaxed text-slate-400">
                                    {{ $product->description }}
                                </p>

                                <div class="mt-5 flex items-center justify-between gap-3">
                                    <span class="text-lg font-bold text-amber-300">
                                        RM {{ number_format($product->price, 2) }}
                                    </span>

                                    <span
                                        class="text-xs {{ $product->stock_quantity > 0 ? 'text-emerald-400' : 'text-red-400' }}"
                                        data-stock-badge
                                        data-product-id="{{ $product->id }}"
                                    >
                                        @if ($product->stock_quantity > 0)
                                            {{ $product->stock_quantity }} in stock
                                        @else
                                            Out of stock
                                        @endif
                                    </span>
                                </div>

                                <label
                                    for="quantity_{{ $product->id }}"
                                    class="mb-2 mt-4 text-sm text-slate-300"
                                >
                                    Quantity
                                </label>

                                <input
                                    id="quantity_{{ $product->id }}"
                                    type="number"
                                    name="items[{{ $product->id }}][quantity]"
                                    data-product-id="{{ $product->id }}"
                                    data-price="{{ $product->price }}"
                                    min="0"
                                    max="{{ $product->stock_quantity }}"
                                    step="1"
                                    value="0"
                                    @disabled($product->stock_quantity < 1)
                                    class="quantity-input w-full rounded-lg border border-slate-700 bg-slate-950 p-3 text-white outline-none focus:border-amber-400 disabled:opacity-40"
                                >
                            </div>
                        </article>
                    @empty
                        <p class="text-slate-400">
                            No products are available right now.
                        </p>
                    @endforelse
                </div>

                @if ($products->isNotEmpty())
                    <section class="mt-8 rounded-xl border border-slate-800 bg-slate-900 p-6">
                        <div class="flex flex-wrap items-center justify-between gap-4">
                            <div>
                                <p class="text-sm text-slate-400">
                                    Estimated total
                                </p>

                                <p id="order-total" class="mt-1 text-3xl font-bold text-amber-300">
                                    RM 0.00
                                </p>

                                <p id="selected-count" class="mt-2 text-sm text-slate-400">
                                    No items selected
                                </p>
                            </div>

                            <button
                                id="submit-order"
                                type="submit"
                                class="rounded-lg bg-amber-500 px-6 py-3 font-semibold text-slate-950 transition hover:bg-amber-400 disabled:cursor-not-allowed disabled:opacity-50"
                            >
                                Place Order
                            </button>
                        </div>
                    </section>
                @endif
            </form>
        </div>
    </main>

    <script>
        (() => {
            const form = document.getElementById('order-form');

            if (!form) return;

            const quantityInputs = [
                ...form.querySelectorAll('.quantity-input')
            ];

            const totalElement = document.getElementById('order-total');
            const countElement = document.getElementById('selected-count');
            const messageElement = document.getElementById('order-message');
            const submitButton = document.getElementById('submit-order');

            const formatRM = (amount) =>
                new Intl.NumberFormat('en-MY', {
                    style: 'currency',
                    currency: 'MYR'
                }).format(amount);

            function updateTotal() {
                let total = 0;
                let count = 0;

                for (const input of quantityInputs) {
                    const quantity = Number(input.value) || 0;
                    const price = Number(input.dataset.price) || 0;

                    total += quantity * price;
                    count += quantity;
                }

                totalElement.textContent = formatRM(total);
                countElement.textContent = count === 1
                    ? '1 item selected'
                    : `${count} items selected`;

                submitButton.disabled = count === 0;
            }

            function showMessage(message, success = false) {
                messageElement.classList.remove(
                    'hidden',
                    'border-red-700',
                    'bg-red-950',
                    'border-emerald-700',
                    'bg-emerald-950'
                );

                messageElement.classList.add(
                    success ? 'border-emerald-700' : 'border-red-700',
                    success ? 'bg-emerald-950' : 'bg-red-950'
                );
                messageElement.textContent = message;

                if (success) {
                    setTimeout(() => {
                        messageElement.classList.add('hidden');
                    }, 3000);
                }
            }

            function updateStock(stockByProductId) {
                if (!stockByProductId) return;

                for (const [productId, stockValue] of Object.entries(stockByProductId)) {
                    const quantity = Number(stockValue);
                    const input = form.querySelector(
                        `.quantity-input[data-product-id="${productId}"]`
                    );
                    const badge = form.querySelector(
                        `[data-stock-badge][data-product-id="${productId}"]`
                    );

                    if (input) {
                        input.max = quantity;
                        input.disabled = quantity < 1;

                        if (Number(input.value) > quantity) {
                            input.value = quantity;
                        }
                    }

                    if (badge) {
                        badge.textContent = quantity > 0
                            ? `${quantity} in stock`
                            : 'Out of stock';
                        badge.classList.toggle(
                            'text-emerald-400',
                            quantity > 0
                        );
                        badge.classList.toggle('text-red-400', quantity <= 0);
                    }
                }
            }

            quantityInputs.forEach((input) => {
                input.addEventListener('input', () => {
                    const max = Number(input.max);
                    const value = Number(input.value);

                    if (input.value !== '' && value < 0) {
                        input.value = 0;
                    }

                    if (input.value !== '' && value > max) {
                        input.value = max;
                    }

                    updateTotal();
                });
            });

            form.addEventListener('submit', async (event) => {
                event.preventDefault();

                if (!form.reportValidity()) return;

                const items = quantityInputs
                    .map((input) => ({
                        product_id: Number(input.dataset.productId),
                        quantity: Number(input.value) || 0
                    }))
                    .filter((item) => item.quantity > 0);

                if (items.length === 0) {
                    showMessage('Please select at least one item.');
                    return;
                }

                const payload = {
                    customer_name: form.elements.customer_name.value.trim(),
                    customer_email: form.elements.customer_email.value.trim(),
                    items
                };

                submitButton.disabled = true;
                submitButton.textContent = 'Processing...';

                try {
                    const response = await fetch('/api/orders', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector(
                                'input[name="_token"]'
                            ).value,
                        },
                        credentials: 'same-origin',
                        body: JSON.stringify(payload),
                    });

                    const result = await response.json();

                    if (!response.ok) {
                        updateStock(result.stock);
                        updateTotal();

                        throw new Error(
                            result.message || 'Failed to create order.'
                        );
                    }

                    const order = result.order;

                    showMessage(
                        `Order placed successfully! Order number: ${order.order_number}. Total: RM ${Number(order.total_amount).toFixed(2)}.`,
                        true
                    );

                    updateStock(result.stock);
                    form.reset();
                    updateTotal();
                } catch (error) {
                    showMessage(
                        error.message || 'Something went wrong. Please try again.'
                    );
                } finally {
                    submitButton.textContent = 'Place Order';
                    updateTotal();
                }
            });

            updateTotal();
        })();
    </script>
</x-layout>
