<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'price',
        'stock',
        'category_id',
        'user_id',
        'image_path',
        'is_active',
        'average_rating',
        'review_count'
    ];

    // Relasi ke Category
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    // Relasi ke User (pemilik produk)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relasi ke Discount (many-to-many)
    public function discounts()
    {
        return $this->belongsToMany(Discount::class);
    }

    // Relasi ke Review (one-to-many)
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    // Relasi ke ProductImage (one-to-many)
    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }

    // Accessor untuk harga yang sudah diformat
    public function getFormattedPriceAttribute()
    {
        return 'Rp ' . number_format($this->price, 0, ',', '.');
    }

    // Accessor untuk harga setelah diskon
    public function getDiscountedPriceAttribute()
    {
        if ($this->discounts->isNotEmpty()) {
            $discount = $this->discounts->first()->discount_percent;
            return $this->price - ($this->price * $discount / 100);
        }
        return $this->price;
    }

    // Accessor untuk URL gambar utama
    public function getImageUrlAttribute()
    {
        return asset('storage/' . $this->image_path);
    }
}