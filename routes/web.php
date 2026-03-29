<?php

use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('products.index');
});

Route::resource('products', ProductController::class);

// Ajax Routes for Image Upload and Delete Management

Route::delete('products/{product}/images/{image}', [ProductController::class, 'destroyImage'])
    ->name('products.images.destroy');

Route::post('products/{product}/images', [ProductController::class, 'storeImages'])
    ->name('products.images.store');
