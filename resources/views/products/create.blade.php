<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Tambah Produk</title>

    <link href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/sb-admin-2.min.css') }}" rel="stylesheet">
</head>

<body>

    <div class="container-fluid mt-4">

        <!-- Page Heading -->
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Tambah Produk</h1>

            <a href="{{ route('products.index') }}"
               class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i>
                Kembali
            </a>
        </div>

        <!-- Form Card -->
        <div class="card shadow mb-4">

            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    Form Tambah Produk
                </h6>
            </div>

            <div class="card-body">

                @if ($errors->any())
                    <div class="alert alert-danger">
                        <strong>Terjadi kesalahan!</strong>

                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('products.store') }}" method="POST" enctype="multipart/form-data">

                    @csrf

                    <!-- Kategori -->
                    <div class="form-group">
                        <label for="category_id">Kategori</label>

                        <select name="category_id"
                                id="category_id"
                                class="form-control"
                                required>

                            <option value="">-- Pilih Kategori --</option>

                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}"
                                    {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach

                        </select>
                    </div>

                    <!-- Nama -->
                    <div class="form-group">
                        <label for="name">Nama Produk</label>

                        <input type="text"
                               name="name"
                               id="name"
                               class="form-control"
                               value="{{ old('name') }}"
                               required>
                    </div>

                    <!-- SKU -->
                    <div class="form-group">
                        <label for="sku">SKU</label>

                        <input type="text"
                               name="sku"
                               id="sku"
                               class="form-control"
                               value="{{ old('sku') }}"
                               required>
                    </div>

                    <!-- Harga -->
                    <div class="form-group">
                        <label for="price">Harga</label>

                        <input type="number"
                               name="price"
                               id="price"
                               class="form-control"
                               value="{{ old('price') }}"
                               min="0"
                               required>
                    </div>

                    <!-- Stok -->
                    <div class="form-group">
                        <label for="stock">Stok</label>

                        <input type="number"
                               name="stock"
                               id="stock"
                               class="form-control"
                               value="{{ old('stock', 0) }}"
                               min="0"
                               required>
                    </div>

                    <div class="form-group">
                        <label for="image">Gambar Produk</label>
                        <input type="file"
                               name="image"
                               id="image"
                               class="form-control-file"
                               accept="image/jpeg,image/png,image/gif,image/svg+xml">
                        <small class="form-text text-muted">Format JPG, PNG, GIF, atau SVG; maksimal 2 MB.</small>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i>
                        Simpan Produk
                    </button>

                    <a href="{{ route('products.index') }}"
                       class="btn btn-secondary">
                        Batal
                    </a>

                </form>

            </div>
        </div>

    </div>

</body>

</html>