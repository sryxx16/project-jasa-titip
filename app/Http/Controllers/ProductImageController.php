<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductImageController extends Controller
{
    public function store(Request $request, Product $product)
{
    $request->validate([
        'images.*' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
    ]);

    foreach ($request->file('images') as $image) {
        $path = $image->store('product_images', 'public');

        $product->images()->create([
            'image_path' => $path,
            'order' => $product->images()->count()
        ]);
    }

    return back()->with('success', 'Gambar berhasil diupload');
}
}
