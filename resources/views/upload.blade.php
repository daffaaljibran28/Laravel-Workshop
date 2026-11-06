@if ($errors->any())
    <div role="alert">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@if (session('success'))
    <p>{{ session('success') }}</p>
    <img src="{{ asset('storage/' . session('path')) }}" alt="Gambar yang diunggah" style="max-width: 300px">
@endif

<form method="POST" action="{{ route('file.upload') }}" enctype="multipart/form-data">
    @csrf
    <input type="file" name="file" accept="image/jpeg,image/png,image/gif,image/svg+xml" required>
    <button type="submit">Upload</button>
</form>