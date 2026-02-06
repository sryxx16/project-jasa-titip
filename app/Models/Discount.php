<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Discount extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'discount_percent',
        'start_date',
        'end_date',
        'is_active',
        'min_order_amount',
        'max_discount_amount',
        'usage_limit',
        'code'
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'is_active' => 'boolean'
    ];

    // Relasi ke Product (many-to-many)
    public function products()
    {
        return $this->belongsToMany(Product::class);
    }

    // Scope untuk diskon aktif
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now());
    }

    // Cek apakah diskon masih berlaku
    public function isValid()
    {
        return $this->is_active &&
               now()->between($this->start_date, $this->end_date);
    }

    // Hitung jumlah diskon
    public function calculateDiscount($amount)
    {
        $discount = $amount * ($this->discount_percent / 100);

        if ($this->max_discount_amount && $discount > $this->max_discount_amount) {
            return $this->max_discount_amount;
        }

        return $discount;
    }
}