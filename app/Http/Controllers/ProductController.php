<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    // Fetch all active products.
    public function index()
    {
        $products = DB::table('products')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->where('products.is_active', true)
            ->select(
                'products.id',
                'products.name',
                'products.slug',
                'products.description',
                'products.price',
                'products.stock_quantity',
                'products.image_path',
                'categories.name as category_name',
                'categories.slug as category_slug',
            )
            ->orderBy('products.name')
            ->get();

        return view('store.index', compact('products'));
    }

    public function store()
    {
        $products = DB::table('products')
            ->leftJoin(
                'categories',
                'products.category_id',
                '=',
                'categories.id',
            )
            ->where('products.is_active', true)
            ->select(
                'products.id',
                'products.name',
                'products.slug',
                'products.description',
                'products.price',
                'products.stock_quantity',
                'products.image_path',
                'categories.name as category_name',
                'categories.slug as category_slug',
            )
            ->orderBy('products.name')
            ->get();

        return view('store', compact('products'));
    }

    // Fetch a single product by its slug.
    public function show(string $slug)
    {
        $product = DB::table('products')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->where('products.slug', $slug)
            ->where('products.is_active', true)
            ->select(
                'products.id',
                'products.name',
                'products.slug',
                'products.description',
                'products.price',
                'products.stock_quantity',
                'products.image_path',
                'categories.name as category_name',
                'categories.slug as category_slug',
            )
            ->first();

        if (!$product) {
            return response()->json(
                [
                    'message' => 'Product not found.',
                ],
                404,
            );
        }

        return response()->json($product);
    }

    public function order(Request $request)
    {
        $items = collect($request->input('items', []))
            ->filter(
                fn($item) => is_array($item) &&
                    (int) ($item['quantity'] ?? 0) > 0,
            )
            ->values()
            ->all();

        $request->merge(['items' => $items]);

        $validated = $request->validate([
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_email' => ['required', 'email', 'max:255'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => [
                'required',
                'integer',
                'distinct',
                'exists:products,id',
            ],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ]);

        $order = DB::transaction(function () use ($validated) {
            // Find an existing customer or create a new one.
            $customer = DB::table('customers')
                ->where('email', $validated['customer_email'])
                ->first();

            if (!$customer) {
                $customerId = DB::table('customers')->insertGetId([
                    'name' => $validated['customer_name'],
                    'email' => $validated['customer_email'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                $customerId = $customer->id;
            }

            $orderItems = [];
            $total = 0;

            foreach ($validated['items'] as $item) {
                // Lock the product row to help prevent overselling.
                $product = DB::table('products')
                    ->where('id', $item['product_id'])
                    ->where('is_active', true)
                    ->lockForUpdate()
                    ->first();

                if (!$product) {
                    throw ValidationException::withMessages([
                        'items' => ['One of the products is unavailable.'],
                    ]);
                }

                if ($product->stock_quantity < $item['quantity']) {
                    throw ValidationException::withMessages([
                        'items' => ["Insufficient stock for {$product->name}."],
                    ]);
                }

                $unitPrice = (float) $product->price;
                $subtotal = round($unitPrice * $item['quantity'], 2);

                $total += $subtotal;

                $orderItems[] = [
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'unit_price' => $product->price,
                ];

                DB::table('products')
                    ->where('id', $product->id)
                    ->decrement('stock_quantity', $item['quantity']);
            }

            $orderNumber = 'ORD-' . Str::upper(Str::random(12));

            $orderId = DB::table('orders')->insertGetId([
                'customer_id' => $customerId,
                'order_number' => $orderNumber,
                'status' => 'pending',
                'total_amount' => $total,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            foreach ($orderItems as $item) {
                DB::table('order_items')->insert([
                    'order_id' => $orderId,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            return [
                'id' => $orderId,
                'order_number' => $orderNumber,
                'status' => 'pending',
                'total_amount' => number_format($total, 2, '.', ''),
            ];
        });

        if ($request->expectsJson()) {
            return response()->json(
                [
                    'message' => 'Order created successfully.',
                    'order' => $order,
                ],
                201,
            );
        }

        return redirect()
            ->route('store')
            ->with('success', 'Order placed successfully!')
            ->with('order', $order);
    }
}
