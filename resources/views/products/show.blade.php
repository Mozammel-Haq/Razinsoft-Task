@extends('layouts.app')

@section('title', $product->name)

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    {{-- Breadcrumb --}}
    <nav class="flex items-center gap-2 text-sm text-gray-500 mb-6">
        <a href="{{ route('products.index') }}" class="hover:text-indigo-600 transition-colors">Products</a>
        <i class="ph ph-caret-right"></i>
        <span class="text-gray-900 font-medium truncate">{{ Str::limit($product->name, 40) }}</span>
    </nav>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">

        {{-- LEFT: Image Gallery --}}
        <div class="space-y-4">
            <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
                {{-- Main Image --}}
                <div class="aspect-video bg-gray-100 flex items-center justify-center">
                    @if($product->images->isNotEmpty())
                        <img id="pgmGalleryMain"
                             src="{{ Storage::disk('public')->exists('products/' . $product->images->first()->filename)
                                 ? Storage::disk('public')->url('products/' . $product->images->first()->filename)
                                 : 'https://placehold.co/800x450/e5e7eb/9ca3af?text=No+Image' }}"
                             alt="{{ $product->name }}"
                             class="w-full h-full object-cover transition-opacity duration-200">
                    @else
                        <div class="text-center text-gray-400">
                            <i class="ph ph-image text-4xl mb-2"></i>
                            <p class="text-sm">No images uploaded</p>
                        </div>
                    @endif
                </div>

                {{-- Thumbnails --}}
                @if($product->images->count() > 1)
                    <div class="flex gap-2 p-3 overflow-x-auto border-t border-gray-100">
                        @foreach($product->images as $i => $image)
                            <button type="button"
                                    class="pgm-gallery__thumb flex-shrink-0 w-16 h-16 border-2 rounded-lg overflow-hidden transition-all {{ $i === 0 ? 'border-indigo-600 ring-2 ring-indigo-200' : 'border-transparent hover:border-gray-300' }}"
                                    data-src="{{ Storage::disk('public')->exists('products/' . $image->filename)
                                        ? Storage::disk('public')->url('products/' . $image->filename)
                                        : 'https://placehold.co/200x150/e5e7eb/9ca3af?text=img' }}"
                                    title="{{ $image->original_name }}">
                                <img src="{{ Storage::disk('public')->exists('products/' . $image->filename)
                                    ? Storage::disk('public')->url('products/' . $image->filename)
                                    : 'https://placehold.co/200x150/e5e7eb/9ca3af?text=img' }}"
                                     alt="{{ $image->original_name }}"
                                     class="w-full h-full object-cover"
                                     loading="lazy">
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Image Count + Manage Link --}}
            <div class="flex items-center justify-between px-1">
                <span class="text-sm text-gray-500 flex items-center gap-1.5">
                    <i class="ph ph-images"></i>
                    {{ $product->images->count() }} {{ Str::plural('image', $product->images->count()) }}
                </span>
                <a href="{{ route('products.edit', $product) }}" class="text-sm text-indigo-600 hover:text-indigo-700 font-medium flex items-center gap-1 transition-colors">
                    <i class="ph ph-pencil-simple"></i> Manage images
                </a>
            </div>
        </div>

        {{-- RIGHT: Product Meta --}}
        <div class="space-y-6">
            {{-- Main Card --}}
            <div class="bg-white border border-gray-200 rounded-xl p-6">
                <div class="flex items-start justify-end gap-3 mb-4">
                    <span class="text-sm text-gray-400">#{{ $product->id }}</span>
                </div>

                <h1 class="font-['Space_Grotesk'] font-bold text-2xl text-gray-900 mb-3">{{ $product->name }}</h1>
                <p class="text-gray-600 leading-relaxed">{{ $product->description }}</p>
            </div>

            {{-- Metadata --}}
            <div class="bg-white border border-gray-200 rounded-xl p-6">
                <div class="flex items-center gap-2 mb-4 pb-4 border-b border-gray-100">
                    <i class="ph-fill ph-info text-indigo-600 text-lg"></i>
                    <h2 class="font-['Space_Grotesk'] font-semibold text-lg text-gray-900">Details</h2>
                </div>

                <div class="space-y-3">
                    <div class="flex items-center justify-between py-2">
                        <span class="text-sm text-gray-500 flex items-center gap-2">
                            <i class="ph ph-calendar-blank"></i> Created
                        </span>
                        <span class="text-sm font-medium text-gray-900">{{ $product->created_at->format('d M Y, H:i') }}</span>
                    </div>
                    <div class="flex items-center justify-between py-2">
                        <span class="text-sm text-gray-500 flex items-center gap-2">
                            <i class="ph ph-pencil-simple"></i> Updated
                        </span>
                        <span class="text-sm font-medium text-gray-900">{{ $product->updated_at->format('d M Y, H:i') }}</span>
                    </div>
                    <div class="flex items-center justify-between py-2">
                        <span class="text-sm text-gray-500 flex items-center gap-2">
                            <i class="ph ph-images"></i> Images
                        </span>
                        <span class="text-sm font-medium text-gray-900">{{ $product->images->count() }} uploaded</span>
                    </div>
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex flex-col sm:flex-row gap-3">
                <a href="{{ route('products.edit', $product) }}" class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-indigo-600 text-white text-sm font-medium rounded-lg transition-all duration-200 hover:bg-indigo-700 active:translate-y-px">
                    <i class="ph ph-pencil-simple"></i> Edit Product
                </a>
                <button type="button"
                        class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-2.5 border border-red-300 text-red-600 text-sm font-medium rounded-lg transition-colors hover:bg-red-50"
                        data-delete-form="delete-form-{{ $product->id }}"
                        data-product-name="{{ $product->name }}">
                    <i class="ph ph-trash"></i> Delete
                </button>
            </div>

            {{-- Back Link --}}
            <a href="{{ route('products.index') }}" class="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-indigo-600 transition-colors">
                <i class="ph ph-arrow-left"></i> Back to Gallery
            </a>
        </div>

    </div>

</div>

{{-- Hidden Delete Form: DELETE to products.destroy --}}
<form id="delete-form-{{ $product->id }}"
      action="{{ route('products.destroy', $product) }}"
      method="POST"
      style="display:none">
    @csrf
    @method('DELETE')
</form>

{{-- Delete Modal --}}
<div id="pgmDeleteModal" class="fixed inset-0 bg-black/50 items-center justify-center z-50 hidden">
    <div class="bg-white rounded-xl max-w-md w-full mx-4 shadow-xl">
        <div class="p-6">
            <div class="w-12 h-12 bg-red-100 rounded-full mx-auto mb-4 flex items-center justify-center">
                <i class="ph-fill ph-trash text-red-600 text-xl"></i>
            </div>
            <h3 class="font-['Space_Grotesk'] text-xl font-semibold text-center text-gray-900 mb-2">
                Delete Product?
            </h3>
            <p class="text-gray-600 text-center text-sm">
                You're about to permanently delete <strong id="pgmDeleteTarget" class="text-gray-900"></strong>.
                All {{ $product->images->count() }} associated {{ Str::plural('image', $product->images->count()) }} will be removed from storage. This action cannot be undone.
            </p>
            <div class="flex gap-3 mt-6">
                <button type="button" class="flex-1 px-4 py-2.5 border border-gray-300 text-gray-700 text-sm font-medium rounded-lg transition-colors hover:bg-gray-50" data-modal-close>
                    Cancel
                </button>
                <button type="button" class="flex-1 px-4 py-2.5 bg-red-600 text-white text-sm font-medium rounded-lg transition-colors hover:bg-red-700" id="pgmConfirmDelete">
                    <i class="ph ph-trash"></i> Yes, Delete
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
{{-- Gallery thumbnail switcher --}}
<script>
document.addEventListener('DOMContentLoaded', () => {
    const mainImg = document.getElementById('pgmGalleryMain');
    const thumbs = document.querySelectorAll('.pgm-gallery__thumb');

    if (mainImg && thumbs.length) {
        thumbs.forEach(thumb => {
            thumb.addEventListener('click', () => {
                const newSrc = thumb.dataset.src;
                mainImg.style.opacity = '0';
                setTimeout(() => {
                    mainImg.src = newSrc;
                    mainImg.style.opacity = '1';
                }, 150);
                thumbs.forEach(t => t.classList.remove('border-indigo-600', 'ring-2', 'ring-indigo-200'));
                thumb.classList.add('border-indigo-600', 'ring-2', 'ring-indigo-200');
            });
        });
    }
});
@endpush
@endsection
