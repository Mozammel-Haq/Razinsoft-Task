<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProductController extends Controller
{
    /**
     * Display a listing of all products.
     */
    public function index(): View
    {
        $products = Product::with(['images' => function ($q) {
            $q->orderBy('sort_order');
        }])->latest()->paginate(12);

        return view('products.index', compact('products'));
    }

    /**
     * Show the form for creating a new product.
     */
    public function create(): View
    {
        return view('products.create');
    }

    /**
     * Store a newly created product in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'required|string',
            'images'      => 'required|array|min:1',
            'images.*'    => 'required|file|mimes:jpeg,jpg,png,webp|max:2048',
        ], [
            'images.required'   => 'Please upload at least one product image.',
            'images.min'        => 'Please upload at least one product image.',
            'images.*.mimes'    => 'Each image must be JPEG, PNG, or WebP format.',
            'images.*.max'      => 'Each image must not exceed 2MB in size.',
        ]);

        DB::transaction(function () use ($validated, $request) {
            $product = Product::create([
                'name'        => $validated['name'],
                'description' => $validated['description'],
            ]);

            $this->handleImageUploads($product, $request->file('images', []));
        });

        return redirect()->route('products.index')
            ->with('success', 'Product created successfully!');
    }

    /**
     * Display the specified product.
     */
    public function show(Product $product): View
    {
        $product->load(['images' => function ($q) {
            $q->orderBy('sort_order');
        }]);

        return view('products.show', compact('product'));
    }

    /**
     * Show the form for editing the specified product.
     */
    public function edit(Product $product): View
    {
        $product->load(['images' => function ($q) {
            $q->orderBy('sort_order');
        }]);

        return view('products.edit', compact('product'));
    }

    /**
     * Update the specified product in storage.
     */
    public function update(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'required|string',
            'images'      => 'nullable|array',
            'images.*'    => 'nullable|file|mimes:jpeg,jpg,png,webp|max:2048',
        ], [
            'images.*.mimes' => 'Each image must be JPEG, PNG, or WebP format.',
            'images.*.max'   => 'Each image must not exceed 2MB in size.',
        ]);

        DB::transaction(function () use ($validated, $request, $product) {
            $product->update([
                'name'        => $validated['name'],
                'description' => $validated['description'],
            ]);

            if ($request->hasFile('images')) {
                $this->handleImageUploads($product, $request->file('images', []));
            }
        });

        return redirect()->route('products.show', $product)
            ->with('success', 'Product updated successfully!');
    }

    /**
     * Remove the specified product from storage.
     */
    public function destroy(Product $product): RedirectResponse
    {
        // The ProductImage model's boot() method handles file deletion
        $product->images->each->delete();
        $product->delete();

        return redirect()->route('products.index')
            ->with('success', 'Product deleted successfully!');
    }

    /**
     * AJAX: Delete a single image from a product.
     */
    public function destroyImage(Product $product, ProductImage $image): JsonResponse
    {
        if ($image->product_id !== $product->id) {
            return response()->json(['error' => 'Image does not belong to this product.'], 403);
        }

        $image->delete();

        return response()->json(['success' => true, 'message' => 'Image removed successfully.']);
    }

    /**
     * AJAX: Upload additional images for an existing product.
     */
    public function storeImages(Request $request, Product $product): JsonResponse
    {
        $request->validate([
            'images'   => 'required|array|min:1',
            'images.*' => 'required|file|mimes:jpeg,jpg,png,webp|max:2048',
        ]);

        $uploaded = $this->handleImageUploads($product, $request->file('images', []));

        $imageData = $uploaded->map(fn($img) => [
            'id'           => $img->id,
            'url'          => $img->url,
            'original_name' => $img->original_name,
        ]);

        return response()->json([
            'success' => true,
            'images'  => $imageData,
        ]);
    }

    /**
     * Handle multiple image uploads for a product.
     */
    private function handleImageUploads(Product $product, array $files)
    {
        $sortOrder = $product->images()->max('sort_order') ?? 0;
        $uploaded  = new Collection();

        foreach ($files as $file) {
            if (! $file || ! $file->isValid()) {
                continue;
            }

            $extension = $file->getClientOriginalExtension();
            $filename  = Str::uuid() . '.' . $extension;

            Storage::disk('public')->putFileAs('products', $file, $filename);

            $image = $product->images()->create([
                'filename'      => $filename,
                'original_name' => $file->getClientOriginalName(),
                'sort_order'    => ++$sortOrder,
            ]);

            $uploaded->push($image);
        }

        return $uploaded;
    }
}
