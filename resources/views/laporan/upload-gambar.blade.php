<!DOCTYPE html>
<html>
<head>
    <title>Acara 31 - Manipulasi Gambar</title>
</head>
<body>

    <h2>Upload & Resize Gambar (300x300)</h2>

    @if(session('success'))
        <p style="color: green;">{{ session('success') }}</p>
    @endif

    <form method="POST" action="{{ route('image.upload') }}" enctype="multipart/form-data">
        @csrf
        <input type="file" name="photo" required>
        <button type="submit">Upload Gambar</button>
    </form>

</body>
</html>