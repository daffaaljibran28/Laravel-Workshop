<?php

namespace App\Http\Controllers;

use App\Events\OrderPlaced;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class KasirController extends Controller
{
    public function dashboard(): View
    {
        return view('kasir.dashboard', [
            'products' => Product::query()->where('stock', '>', 0)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:completed,held'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'distinct', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:100000'],
            'payment_amount' => ['nullable', 'required_if:status,completed', 'integer', 'min:0'],
        ]);

        $user = $request->user();
        $order = DB::transaction(function () use ($validated, $user): Order {
            $products = Product::query()
                ->whereIn('id', collect($validated['items'])->pluck('product_id'))
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $total = 0;
            $lines = [];

            foreach ($validated['items'] as $item) {
                $product = $products->get($item['product_id']);
                $quantity = (int) $item['quantity'];

                if (! $product) {
                    throw ValidationException::withMessages([
                        'items' => 'Salah satu produk sudah tidak tersedia.',
                    ]);
                }

                if ($validated['status'] === 'completed' && $product->stock < $quantity) {
                    throw ValidationException::withMessages([
                        'items' => "Stok {$product->name} tidak mencukupi. Tersedia {$product->stock}.",
                    ]);
                }

                $subtotal = $product->price * $quantity;
                $total += $subtotal;
                $lines[] = [
                    'product' => $product,
                    'quantity' => $quantity,
                    'subtotal' => $subtotal,
                ];
            }

            if ($validated['status'] === 'completed' && (int) $validated['payment_amount'] < $total) {
                throw ValidationException::withMessages([
                    'payment_amount' => 'Pembayaran tidak boleh kurang dari total transaksi.',
                ]);
            }

            $order = Order::create([
                'user_id' => $user->id,
                'total' => $total,
                'payment_amount' => $validated['status'] === 'completed'
                    ? (int) $validated['payment_amount']
                    : 0,
                'status' => $validated['status'],
            ]);
            $order->order_number = 'TRX-'.now()->format('Ymd').'-'.str_pad((string) $order->id, 6, '0', STR_PAD_LEFT);
            $order->save();

            foreach ($lines as $line) {
                $order->items()->create([
                    'product_id' => $line['product']->id,
                    'product_name' => $line['product']->name,
                    'price' => $line['product']->price,
                    'quantity' => $line['quantity'],
                    'subtotal' => $line['subtotal'],
                ]);

                if ($validated['status'] === 'completed') {
                    $line['product']->decrement('stock', $line['quantity']);
                }
            }

            return $order;
        });

        if ($order->status === 'completed') {
            event(new OrderPlaced($order, $user));
        }

        $message = $order->status === 'held'
            ? "Transaksi {$order->order_number} ditahan. Event tidak dikirim."
            : "Transaksi {$order->order_number} berhasil disimpan.";

        if ($request->expectsJson()) {
            return response()->json([
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'status' => $order->status,
                'total' => $order->total,
                'message' => $message,
            ], 201);
        }

        return back()->with('success', $message);
    }
}
