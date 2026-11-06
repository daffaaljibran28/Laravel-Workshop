<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Dashboard Kasir
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="space-y-6 p-6 text-gray-900">
                    <div>
                        <h3 class="text-lg font-medium">Transaksi kasir</h3>
                        <p class="mt-1 text-sm text-gray-600">
                            Pilih barang dan jumlahnya. Stok hanya berkurang jika transaksi diselesaikan.
                        </p>
                    </div>

                    @if (session('success'))
                        <p class="rounded-md bg-green-50 p-3 text-sm text-green-800" role="status">
                            {{ session('success') }}
                        </p>
                    @endif

                    @if ($products->isEmpty())
                        <p class="rounded-md bg-yellow-50 p-3 text-sm text-yellow-800">
                            Belum ada barang dengan stok tersedia. Tambahkan barang dan stok sebelum mencoba transaksi.
                        </p>
                    @endif

                    <form
                        id="order-form"
                        action="{{ route('kasir.transaksi.store') }}"
                        method="POST"
                        class="max-w-3xl space-y-4"
                    >
                        @csrf
                        <input id="order-status" name="status" type="hidden" value="completed">

                        <div id="order-items" class="space-y-3">
                            <div class="order-item grid gap-3 rounded-md border p-3 sm:grid-cols-[1fr_8rem_auto] sm:items-end">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Barang</label>
                                    <select name="items[0][product_id]" required class="order-product mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                        <option value="">Pilih barang</option>
                                        @foreach ($products as $product)
                                            <option value="{{ $product->id }}">
                                                {{ $product->name }} ({{ $product->sku }}) — Rp {{ number_format($product->price, 0, ',', '.') }} · stok {{ $product->stock }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Jumlah</label>
                                    <input name="items[0][quantity]" type="number" min="1" value="1" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                </div>
                                <button type="button" class="remove-order-item rounded-md border px-3 py-2 text-sm text-gray-700 hover:bg-gray-50" aria-label="Hapus barang">
                                    Hapus
                                </button>
                            </div>
                        </div>

                        <template id="order-item-template">
                            <div class="order-item grid gap-3 rounded-md border p-3 sm:grid-cols-[1fr_8rem_auto] sm:items-end">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Barang</label>
                                    <select name="items[__INDEX__][product_id]" required class="order-product mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                        <option value="">Pilih barang</option>
                                        @foreach ($products as $product)
                                            <option value="{{ $product->id }}">
                                                {{ $product->name }} ({{ $product->sku }}) — Rp {{ number_format($product->price, 0, ',', '.') }} · stok {{ $product->stock }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Jumlah</label>
                                    <input name="items[__INDEX__][quantity]" type="number" min="1" value="1" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                </div>
                                <button type="button" class="remove-order-item rounded-md border px-3 py-2 text-sm text-gray-700 hover:bg-gray-50" aria-label="Hapus barang">
                                    Hapus
                                </button>
                            </div>
                        </template>

                        <button id="add-order-item" type="button" class="rounded-md border border-indigo-600 px-4 py-2 text-sm font-semibold text-indigo-700 hover:bg-indigo-50" @disabled($products->isEmpty())>
                            Tambah barang
                        </button>

                        <div class="grid max-w-md gap-4 sm:grid-cols-2">
                            <div>
                                <label for="payment_amount" class="block text-sm font-medium text-gray-700">Uang dibayar (Rp)</label>
                                <input id="payment_amount" name="payment_amount" type="number" min="0" step="1" value="0" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                <p class="mt-1 text-xs text-gray-500">Wajib mencukupi total saat transaksi diselesaikan.</p>
                            </div>
                        </div>

                        <div class="flex flex-wrap gap-3">
                            <button
                                type="submit"
                                data-order-status="completed"
                                @disabled($products->isEmpty())
                                class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500 disabled:cursor-not-allowed disabled:opacity-50"
                            >
                                Selesaikan & kirim event
                            </button>
                            <button
                                type="submit"
                                data-order-status="held"
                                @disabled($products->isEmpty())
                                class="rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-50"
                            >
                                Tahan transaksi
                            </button>
                        </div>
                    </form>

                    <p id="order-form-status" class="text-sm" role="status" aria-live="polite"></p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>