<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FileController extends Controller
{
    public function index(): View
    {
        return view('upload');
    }

    public function upload(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'file' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $path = $validated['file']->store('uploads', 'public');

        return back()
            ->with('success', 'Gambar berhasil diunggah.')
            ->with('path', $path);
    }
}
