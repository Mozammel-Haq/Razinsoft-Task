@extends('layouts.app')

@section('title', 'Edit — ' . $product->name)

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    {{-- Hidden product ID for JS --}}
    <input type="hidden" id="pgmProductId" value="{{ $product->id }}">

    {{-- Pass AJAX route config to JS --}}
    <script>
        window.pgmRoutes = {
            imageDestroy: "{{ route('products.images.destroy', ['product' => $product->id, 'image' => '__IMAGE__']) }}"
        };
    </script>

    {{-- Page Header --}}
    <div class="mb-8">
        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
            <div>
                <nav class="flex items-center gap-2 text-sm text-gray-500 mb-3 flex-wrap">
                    <a href="{{ route('products.index') }}" class="hover:text-indigo-600 transition-colors">Products</a>
                    <i class="ph ph-caret-right"></i>
                    <a href="{{ route('products.show', $product) }}" class="hover:text-indigo-600 transition-colors truncate max-w-[180px] sm:max-w-none">{{ Str::limit($product->name, 30) }}</a>
                    <i class="ph ph-caret-right"></i>
                    <span class="text-gray-900 font-medium">Edit</span>
                </nav>
                <h1 class="font-['Space_Grotesk'] text-3xl sm:text-4xl font-bold tracking-tight text-gray-900">
                    Edit <span class="text-indigo-600">Product</span>
                </h1>
                <p class="text-gray-600 mt-2 text-sm">
                    Update details or manage images below.
                </p>
            </div>
            <a href="{{ route('products.show', $product) }}" class="inline-flex items-center gap-2 px-4 py-2 border border-gray-300 text-gray-700 text-sm font-medium rounded-lg transition-colors hover:bg-gray-50">
                <i class="ph ph-eye"></i> View Product
            </a>
        </div>
    </div>

    {{-- Form: PUT to products.update --}}
    <form action="{{ route('products.update', $product) }}" method="POST" enctype="multipart/form-data" id="pgmProductForm" novalidate class="space-y-8">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

            {{-- Left: Details --}}
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white border border-gray-200 rounded-xl p-6">
                    <div class="flex items-center gap-2 mb-6 pb-4 border-b border-gray-100">
                        <i class="ph-fill ph-tag text-indigo-600 text-lg"></i>
                        <h2 class="font-['Space_Grotesk'] font-semibold text-lg text-gray-900">Product Details</h2>
                    </div>

                    <div class="mb-5">
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                            Product Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="name" id="name"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors @error('name') border-red-300 @enderror"
                            value="{{ old('name', $product->name) }}" placeholder="Product name">
                        @error('name')
                            <p class="mt-2 text-sm text-red-600 flex items-center gap-1">
                                <i class="ph-fill ph-warning-circle"></i> {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                            Description <span class="text-red-500">*</span>
                        </label>
                        <textarea name="description" id="description" rows="7"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors resize-y @error('description') border-red-300 @enderror"
                            placeholder="Product description…">{{ old('description', $product->description) }}</textarea>
                        @error('description')
                            <p class="mt-2 text-sm text-red-600 flex items-center gap-1">
                                <i class="ph-fill ph-warning-circle"></i> {{ $message }}
                            </p>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- Right: Images --}}
            <div class="space-y-6">
                <div class="bg-white border border-gray-200 rounded-xl p-6">
                    <div class="flex items-center justify-between gap-2 mb-6 pb-4 border-b border-gray-100">
                        <div class="flex items-center gap-2">
                            <i class="ph-fill ph-images text-indigo-600 text-lg"></i>
                            <h2 class="font-['Space_Grotesk'] font-semibold text-lg text-gray-900">Images</h2>
                        </div>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-700">
                            {{ $product->images->count() }} saved
                        </span>
                    </div>

                    {{-- Hidden spans for saved images (JS reads these for AJAX delete) --}}
                    @foreach($product->images as $image)
                        <span style="display:none"
                              data-saved-image="{{ $image->id }}"
                              data-src="{{ Storage::disk('public')->exists('products/' . $image->filename)
                                  ? Storage::disk('public')->url('products/' . $image->filename)
                                  : 'https://placehold.co/400x300/e5e7eb/9ca3af?text=No+Image' }}"
                              data-name="{{ $image->original_name }}"></span>
                    @endforeach

                    {{-- Preview Grid (populated by JS) --}}
                    <div id="pgmPreviewGrid" class="grid grid-cols-2 gap-3"></div>

                    <div class="border-t border-gray-100 my-5"></div>

                    <p class="text-sm font-medium text-gray-700 mb-3">Add More Images</p>

                    {{-- Drop Zone --}}
                    <div id="pgmDropzone" class="border-2 border-dashed border-gray-300 rounded-xl p-5 text-center cursor-pointer transition-colors hover:border-indigo-400 hover:bg-indigo-50/50">
                        <input type="file" name="images[]" id="pgmFileInput" multiple accept="image/jpeg,image/jpg,image/png,image/webp" class="hidden">
                        <div class="flex flex-col items-center gap-2.5">
                            <div class="w-10 h-10 bg-indigo-100 rounded-full flex items-center justify-center">
                                <i class="ph ph-cloud-arrow-up text-indigo-600 text-lg"></i>
                            </div>
                            <p class="text-sm text-gray-700">
                                Drop to add images or <span class="text-indigo-600 font-medium">click to browse</span>
                            </p>
                            <p class="text-xs text-gray-500">JPEG/PNG/WebP · max 2 MB</p>
                        </div>
                    </div>

                    @error('images.*')
                        <p class="mt-3 text-sm text-red-600 flex items-center gap-1">
                            <i class="ph-fill ph-warning-circle"></i> {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- Submit --}}
                <div class="flex gap-3">
                    <button type="submit" class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-indigo-600 text-white text-sm font-medium rounded-lg transition-all duration-200 hover:bg-indigo-700 active:translate-y-px">
                        <i class="ph ph-floppy-disk"></i> Save Changes
                    </button>
                    <a href="{{ route('products.show', $product) }}" class="px-4 py-2.5 border border-gray-300 text-gray-700 text-sm font-medium rounded-lg transition-colors hover:bg-gray-50">
                        Cancel
                    </a>
                </div>
            </div>

        </div>
    </form>

</div>

{{-- Preview Item Template --}}
<template id="pgmPreviewTemplate">
    <div class="pgm-preview-item relative group bg-gray-50 border border-gray-200 rounded-lg overflow-hidden aspect-square">
        <img class="w-full h-full object-cover" src="" alt="">
        <div class="absolute inset-0 bg-black/0 group-hover:bg-black/10 transition-colors"></div>
        <button type="button" class="pgm-preview-remove absolute top-2 right-2 w-7 h-7 bg-white/90 hover:bg-white rounded-full flex items-center justify-center text-gray-600 hover:text-red-600 transition-colors shadow-sm">
            <i class="ph ph-x text-sm"></i>
        </button>
        <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/60 to-transparent p-2">
            <p class="text-white text-xs truncate pgm-preview-name"></p>
        </div>
    </div>
</template>
@endsection
