@extends('layouts.app')

@section('title', 'Add New Product')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    {{-- Page Header --}}
    <div class="mb-8">
        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
            <div>
                <nav class="flex items-center gap-2 text-sm text-gray-500 mb-3">
                    <a href="{{ route('products.index') }}" class="hover:text-indigo-600 transition-colors">Products</a>
                    <i class="ph ph-caret-right"></i>
                    <span class="text-gray-900 font-medium">New Product</span>
                </nav>
                <h1 class="font-['Space_Grotesk'] text-3xl sm:text-4xl font-bold tracking-tight text-gray-900">
                    Add <span class="text-indigo-600">New Product</span>
                </h1>
                <p class="text-gray-600 mt-2 text-sm">
                    Fill in the details and upload at least one image to get started.
                </p>
            </div>
        </div>
    </div>

    {{-- Form: POST to products.store --}}
    <form action="{{ route('products.store') }}" method="POST" enctype="multipart/form-data" id="pgmProductForm" novalidate class="space-y-8">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

            {{-- Left: Product Details --}}
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white border border-gray-200 rounded-xl p-6">
                    <div class="flex items-center gap-2 mb-6 pb-4 border-b border-gray-100">
                        <i class="ph-fill ph-tag text-indigo-600 text-lg"></i>
                        <h2 class="font-['Space_Grotesk'] font-semibold text-lg text-gray-900">Product Details</h2>
                    </div>

                    {{-- Name --}}
                    <div class="mb-5">
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                            Product Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="name" id="name"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors @error('name') border-red-300 @enderror"
                            value="{{ old('name') }}" placeholder="Product Description..." autofocus>
                        @error('name')
                            <p class="mt-2 text-sm text-red-600 flex items-center gap-1">
                                <i class="ph-fill ph-warning-circle"></i> {{ $message }}
                            </p>
                        @enderror
                    </div>

                    {{-- Description --}}
                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                            Description <span class="text-red-500">*</span>
                        </label>
                        <textarea name="description" id="description" rows="7"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors resize-y @error('description') border-red-300 @enderror"
                            placeholder="Write a detailed product description…">{{ old('description') }}</textarea>
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
                    <div class="flex items-center gap-2 mb-6 pb-4 border-b border-gray-100">
                        <i class="ph-fill ph-images text-indigo-600 text-lg"></i>
                        <h2 class="font-['Space_Grotesk'] font-semibold text-lg text-gray-900">Product Images</h2>
                    </div>

                    {{-- Drop Zone --}}
                    <div id="pgmDropzone" class="border-2 border-dashed border-gray-300 rounded-xl p-6 text-center cursor-pointer transition-colors hover:border-indigo-400 hover:bg-indigo-50/50">
                        <input type="file" name="images[]" id="pgmFileInput" multiple accept="image/jpeg,image/jpg,image/png,image/webp" class="hidden">
                        <div class="flex flex-col items-center gap-3">
                            <div class="w-12 h-12 bg-indigo-100 rounded-full flex items-center justify-center">
                                <i class="ph ph-cloud-arrow-up text-indigo-600 text-xl"></i>
                            </div>
                            <div>
                                <p class="font-medium text-gray-900">Drop images here</p>
                                <p class="text-sm text-gray-500 mt-1">
                                    or <span class="text-indigo-600 font-medium">click to browse</span><br>
                                    JPEG · PNG · WebP · max <strong>2 MB</strong> each
                                </p>
                            </div>
                        </div>
                    </div>

                    @error('images')
                        <p class="mt-3 text-sm text-red-600 flex items-center gap-1">
                            <i class="ph-fill ph-warning-circle"></i> {{ $message }}
                        </p>
                    @enderror
                    @error('images.*')
                        <p class="mt-2 text-sm text-red-600 flex items-center gap-1">
                            <i class="ph-fill ph-warning-circle"></i> {{ $message }}
                        </p>
                    @enderror

                    {{-- Preview Grid --}}
                    <div id="pgmPreviewGrid" class="grid grid-cols-2 gap-3 mt-5"></div>

                    <p class="text-xs text-gray-500 mt-4 flex items-start gap-1.5">
                        <i class="ph ph-info text-gray-400 mt-0.5"></i>
                        Images are stored in <code class="bg-gray-100 px-1.5 py-0.5 rounded text-gray-700">storage/app/public/products</code>. At least 1 image required.
                    </p>
                </div>

                {{-- Submit Actions --}}
                <div class="flex gap-3">
                    <button type="submit" class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-indigo-600 text-white text-sm font-medium rounded-lg transition-all duration-200 hover:bg-indigo-700 active:translate-y-px">
                        <i class="ph ph-floppy-disk"></i> Save Product
                    </button>
                    <a href="{{ route('products.index') }}" class="px-4 py-2.5 border border-gray-300 text-gray-700 text-sm font-medium rounded-lg transition-colors hover:bg-gray-50">
                        Cancel
                    </a>
                </div>
            </div>

        </div>
    </form>

</div>

{{-- Preview Item Template (used by JS) --}}
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
