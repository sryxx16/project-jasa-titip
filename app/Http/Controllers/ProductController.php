<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Discount;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with(['category', 'discounts', 'reviews', 'images'])
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->where('is_active', true);

        // Filter kategori
        if ($request->has('category')) {
            $query->where('category_id', $request->category);
        }

        // Filter pencarian
        if ($request->has('search')) {
            $query->where('name', 'like', '%'.$request->search.'%');
        }

        // Filter harga
        if ($request->has('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }

        // Sorting
        $sort = $request->input('sort', 'newest');
        switch ($sort) {
            case 'highest_price':
                $query->orderBy('price', 'desc');
                break;
            case 'lowest_price':
                $query->orderBy('price', 'asc');
                break;
            case 'top_rated':
                $query->orderBy('reviews_avg_rating', 'desc');
                break;
            default:
                $query->latest();
        }

        $products = $query->paginate(12);

        return view('products.index', [
            'products' => $products,
            'categories' => Category::all(),
            'selectedCategory' => $request->category,
            'searchQuery' => $request->search,
            'sortOption' => $sort
        ]);
    }

    public function show(Product $product)
    {
        $product->load(['category', 'discounts', 'reviews.user', 'images'])
            ->loadAvg('reviews', 'rating')
            ->loadCount('reviews');

        $relatedProducts = Product::where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->inRandomOrder()
            ->limit(4)
            ->get();

        return view('products.show', [
            'product' => $product,
            'relatedProducts' => $relatedProducts
        ]);
    }

   public function discount(Request $request)
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

    // Hitung jumlah diskon aktif
    $activeDiscountsCount = Discount::where('is_active', true)
        ->where('start_date', '<=', now())
        ->where('end_date', '>=', now())
        ->count();

    // Hitung jumlah produk yang memiliki diskon aktif
    $discountedProductsCount = Product::whereHas('discounts', function($query) {
        $query->where('is_active', true)
              ->where('start_date', '<=', now())
              ->where('end_date', '>=', now());
    })->count();

    // Ambil kategori yang memiliki produk diskon
    $discountCategories = Category::withCount(['products as discounted_products_count' => function ($query) {
        $query->whereHas('discounts', function ($q) {
            $q->where('is_active', true)
              ->where('start_date', '<=', now())
              ->where('end_date', '>=', now());
        });
    }])
    ->having('discounted_products_count', '>', 0)
    ->take(4)
    ->get()
    ->map(function ($category) {
        $category->icon = $this->getCategoryIcon($category->id); // jika belum ada, bikin fungsi ini
        return $category;
    });

    return view('products.discount', [
        'products' => $products,
        'categories' => Category::all(),
        'isDiscountPage' => true,
        'activeDiscountsCount' => $activeDiscountsCount,
        'discountedProductsCount' => $discountedProductsCount,
        'discountCategories' => $discountCategories,
    ]);
}
private function getCategoryIcon($categoryId)
{
    $icons = [
        1 => '📱',
        2 => '👟',
        3 => '🧴',
        4 => '🎧',
        5 => '👕',
        // ... dan seterusnya
    ];

    return $icons[$categoryId] ?? '🛒'; // default icon
}
}