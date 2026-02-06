<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function store(Request $request, Product $product)
{
    $request->validate([
        'rating' => 'required|integer|between:1,5',
        'comment' => 'required|string|max:500'
    ]);

    $product->reviews()->create([
        'user_id' => auth()->id(),
        'rating' => $request->rating,
        'comment' => $request->comment
    ]);

    // Update product rating
    $product->update([
        'average_rating' => $product->reviews()->avg('rating'),
        'review_count' => $product->reviews()->count()
    ]);

    return back()->with('success', 'Ulasan berhasil ditambahkan');
}

}
