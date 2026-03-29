<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'description'];

    public function images()
    {
        return $this->hasMany(ProductImage::class, 'product_id');
    }

    // Defining the Primary (Main) Image

    public function primaryImage()
    {
        return $this->images()->orderBy('sort_order')->first();
    }
}
