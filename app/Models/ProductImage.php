<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class ProductImage extends Model
{
    use HasFactory;
    protected $fillable = [
        'product_id',
        'filename',
        'original_name',
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    // defining a public url
    public function getUrlAttribute(): string
    {
        return Storage::disk('public')->url('products/' . $this->filename);
    }

    // Model Event Deleting would delete from Storage Disk as well
    protected static function boot(): void
    {
        parent::boot();
        static::deleting(function (ProductImage $image) {
            Storage::disk('public')->delete('products/' . $image->filename);
        });
    }
}
