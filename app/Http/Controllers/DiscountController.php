<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Discount;
use App\Models\Product;
use Illuminate\Http\Request;

class DiscountController extends Controller
{


    // Menampilkan semua diskon aktif
    public function index(Request $request)
{
    // Produk dengan diskon aktif
    $products = Product::whereHas('discounts', function($query) {
        $query->where('is_active', true)
              ->where('start_date', '<=', now())
              ->where('end_date', '>=', now());
    })
    ->with(['category', 'discounts'])
    ->withAvg('reviews', 'rating')
    ->withCount('reviews')
    ->paginate(12);

    // Kategori dengan diskon
    $discountCategories = Category::withCount(['products' => function($query) {
        $query->whereHas('discounts', function($q) {
            $q->where('is_active', true)
              ->where('start_date', '<=', now())
              ->where('end_date', '>=', now());
        });
    }])
    ->having('products_count', '>', 0)
    ->orderByDesc('products_count')
    ->take(4)
    ->get()
    ->map(function($category) {
        $category->icon = $this->getCategoryIcon($category->id);
        return $category;
    });

    return view('discounts.index', [
        'products' => $products,
        'discountCategories' => $discountCategories,
        'activeDiscountsCount' => Discount::active()->count(),
        'discountedProductsCount' => Product::whereHas('discounts', function($query) {
            $query->active();
        })->count()
    ]);
}

private function getCategoryIcon($categoryId)
{
    $icons = [
        1 => '👕',
        2 => '📱',
        3 => '💄',
        4 => '👜',
        5 => '👟',
        6 => '🧸'
    ];

    return $icons[$categoryId] ?? '🛍️';
}

    // Menampilkan detail diskon
    public function show(Discount $discount)
    {
        $discount->load(['products' => function($query) {
            $query->where('is_active', true)
                ->withAvg('reviews', 'rating')
                ->withCount('reviews');
        }]);

        return view('discounts.show', [
            'discount' => $discount,
            'relatedDiscounts' => Discount::active()
                ->where('id', '!=', $discount->id)
                ->inRandomOrder()
                ->limit(3)
                ->get()
        ]);
    }

    // Validasi kode diskon (API)
    public function validateCode(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
            'amount' => 'required|numeric'
        ]);

        $discount = Discount::where('code', $request->code)
            ->active()
            ->first();

        if (!$discount) {
            return response()->json([
                'valid' => false,
                'message' => 'Kode diskon tidak valid atau sudah kadaluarsa'
            ]);
        }

        return response()->json([
            'valid' => true,
            'discount' => [
                'name' => $discount->name,
                'percent' => $discount->discount_percent,
                'amount' => $discount->calculateDiscount($request->amount),
                'code' => $discount->code
            ]
        ]);
    }

    public function discount(Request $request)
{
    $products = Product::whereHas('discounts', function($query) {
        $query->where('is_active', true)
              ->where('start_date', '<=', now())
              ->where('end_date', '>=', now());
    })
    ->with(['category', 'discounts'])
    ->withAvg('reviews', 'rating')
    ->withCount('reviews')
    ->paginate(12);

    $activeDiscountsCount = Discount::where('is_active', true)
        ->where('start_date', '<=', now())
        ->where('end_date', '>=', now())
        ->count();

    $discountedProductsCount = Product::whereHas('discounts', function($query) {
        $query->where('is_active', true)
              ->where('start_date', '<=', now())
              ->where('end_date', '>=', now());
    })->count();

    // Tambahkan ini:
    $discountCategories = Category::withCount(['products' => function($query) {
        $query->whereHas('discounts', function($q) {
            $q->where('is_active', true)
              ->where('start_date', '<=', now())
              ->where('end_date', '>=', now());
        });
    }])
    ->having('products_count', '>', 0)
    ->orderByDesc('products_count')
    ->take(4)
    ->get()
    ->map(function($category) {
        $category->icon = $this->getCategoryIcon($category->id);
        return $category;
    });

    return view('products.discount', [
        'products' => $products,
        'categories' => Category::all(),
        'isDiscountPage' => true,
        'activeDiscountsCount' => $activeDiscountsCount,
        'discountedProductsCount' => $discountedProductsCount,
        'discountCategories' => $discountCategories // ✅ dikirim ke view
    ]);
}

}
