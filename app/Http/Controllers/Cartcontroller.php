<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class Cartcontroller extends Controller
{
    public function addToCart(Request $request, Product $product)
{
    $cart = session()->get('cart', []);

    // Cek apakah produk sudah ada di keranjang
    if (isset($cart[$product->id])) {
        $cart[$product->id]['quantity']++;
    } else {
        $cart[$product->id] = [
            "name" => $product->name,
            "quantity" => 1,
            "price" => $product->current_price,
            "image" => $product->main_image,
            "max_stock" => $product->stock
        ];
    }

    session()->put('cart', $cart);
    return redirect()->back()->with('success', 'Produk berhasil ditambahkan ke keranjang');
}
}
