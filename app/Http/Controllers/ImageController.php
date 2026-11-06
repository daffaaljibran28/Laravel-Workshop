<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Intervention\Image\Facades\Image;

class ImageController extends Controller
{
    public function index()
    {
        return view('laporan.upload-gambar');
    }

    public function testResize()
    {
        $path = public_path('sample.jpg');

        return Image::make($path)->resize(300, 300)->response('jpg');
    }

    public function uploadAndResize(Request $request)
    {
        $request->validate([
            'photo' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $file = $request->file('photo');
        $filename = time() . '.' . $file->getClientOriginalExtension();

        $image = Image::make($file)->resize(300, 300);

        $uploadPath = public_path('uploads');
        if (!file_exists($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }

        $image->save($uploadPath . '/' . $filename, 75);

        return back()
            ->with('success', 'Gambar berhasil di-upload dan di-resize!')
            ->with('uploaded_image', $filename);
    }
}