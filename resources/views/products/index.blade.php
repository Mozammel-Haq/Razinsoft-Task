@extends('layouts.app')

@section('title', 'All Products')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    {{-- Page Header --}}
    <div class="mb-8">
        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
            <div>
                <h1 class="font-['Space_Grotesk'] text-3xl sm:text-4xl font-bold tracking-tight text-gray-900">
                    Product <span class="text-indigo-600">Gallery</span>
                </h1>
                <p class="text-gray-600 mt-2 text-sm">
                    Manage your product catalogue — add, edit, and organise with images.
                </p>
            </div>
            <a href="{{ route('products.create') }}"
               class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 text-white text-sm font-medium transition-all duration-200 hover:bg-indigo-700 active:translate-y-px self-start">
                <i class="ph ph-plus"></i> Add Product
            </a>
        </div>
    </div>

    {{-- Stats Row --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
        <div class="bg-white border border-gray-200 p-5">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-indigo-100 flex items-center justify-center">
                    <i class="ph-fill ph-package text-indigo-600 text-xl"></i>
                </div>
                <div>
                    <div class="font-['Space_Grotesk'] text-2xl font-bold text-gray-900">{{ $products->total() }}</div>
                    <div class="text-xs text-gray-500 uppercase tracking-wide">Total Products</div>
                </div>
            </div>
        </div>

        <div class="bg-white border border-gray-200 p-5">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-amber-100 flex items-center justify-center">
                    <i class="ph-fill ph-images text-amber-600 text-xl"></i>
                </div>
                <div>
                    <div class="font-['Space_Grotesk'] text-2xl font-bold text-gray-900">
                        {{ $products->sum(fn($p) => $p->images->count()) }}
                    </div>
                    <div class="text-xs text-gray-500 uppercase tracking-wide">Total Images</div>
                </div>
            </div>
        </div>

        <div class="bg-white border border-gray-200 p-5">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-emerald-100 flex items-center justify-center">
                    <i class="ph-fill ph-calendar-blank text-emerald-600 text-xl"></i>
                </div>
                <div>
                    <div class="font-['Space_Grotesk'] text-2xl font-bold text-gray-900">{{ now()->format('d M Y') }}</div>
                    <div class="text-xs text-gray-500 uppercase tracking-wide">Today's Date</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Product Grid --}}
    @if($products->count() > 0)
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @foreach($products as $product)
                @php $thumb = $product->images->first(); @endphp

                <div class="bg-white border border-gray-200 overflow-hidden flex flex-col">
                    {{-- Gallery thumbnail --}}
                    <a href="{{ route('products.show', $product) }}" class="block bg-gray-100 aspect-video overflow-hidden">
                        @if($thumb)
                            <img class="w-full h-full object-cover transition-all duration-300 hover:scale-105"
                                 src="{{ Storage::disk('public')->exists('products/' . $thumb->filename)
                                        ? Storage::disk('public')->url('products/' . $thumb->filename)
                                        : 'https://placehold.co/600x400/e5e7eb/9ca3af?text=No+Image' }}"
                                 alt="{{ $product->name }}">
                        @else
                            <div class="w-full h-full flex flex-col items-center justify-center gap-2 text-gray-400">
                                <i class="ph ph-image text-3xl"></i>
                                <span class="text-xs">No images yet</span>
                            </div>
                        @endif

                        @if($product->images->count() > 0)
                            <div class="relative">
                                <div class="absolute bottom-2 right-2 bg-black/70 px-2 py-1 text-white text-xs flex items-center gap-1">
                                    <i class="ph ph-images text-xs"></i>
                                    {{ $product->images->count() }}
                                </div>
                            </div>
                        @endif
                    </a>

                    {{-- Body --}}
                    <div class="p-4 flex-1">
                        <a href="{{ route('products.show', $product) }}" class="block group">
                            <h2 class="font-['Space_Grotesk'] font-semibold text-lg text-gray-900 group-hover:text-indigo-600 transition-colors duration-200 line-clamp-1">
                                {{ $product->name }}
                            </h2>
                        </a>
                        <p class="text-gray-600 text-sm mt-2 line-clamp-2">
                            {{ $product->description }}
                        </p>
                    </div>

                    {{-- Actions --}}
                    <div class="border-t border-gray-100 p-3 flex items-center gap-2">
                        <a href="{{ route('products.show', $product) }}"
                           class="flex-1 inline-flex items-center justify-center gap-1 px-3 py-1.5 border border-gray-300 text-gray-700 text-sm font-medium transition-colors duration-200 hover:bg-gray-50">
                            <i class="ph ph-eye"></i> View
                        </a>
                        <a href="{{ route('products.edit', $product) }}"
                           class="flex-1 inline-flex items-center justify-center gap-1 px-3 py-1.5 border border-gray-300 text-gray-700 text-sm font-medium transition-colors duration-200 hover:bg-gray-50">
                            <i class="ph ph-pencil-simple"></i> Edit
                        </a>
                        <button type="button"
                                class="px-3 py-1.5 text-red-600 transition-colors duration-200 hover:bg-red-50"
                                data-delete-form="delete-form-{{ $product->id }}"
                                data-product-name="{{ $product->name }}">
                            <i class="ph ph-trash"></i>
                        </button>
                    </div>

                    {{-- Hidden delete form --}}
                    <form id="delete-form-{{ $product->id }}"
                          action="{{ route('products.destroy', $product) }}"
                          method="POST"
                          style="display:none">
                        @csrf
                        @method('DELETE')
                    </form>
                </div>
            @endforeach
        </div>

        {{-- Pagination --}}
        @if($products->hasPages())
            <div class="mt-8">
                {{ $products->links() }}
            </div>
        @endif
    @else
        {{-- Empty State --}}
        <div class="bg-white border border-gray-200 py-16 px-4 text-center">
            <div class="w-20 h-20 bg-gray-100 mx-auto mb-4 flex items-center justify-center">
                <i class="ph ph-storefront text-4xl text-gray-400"></i>
            </div>
            <h3 class="font-['Space_Grotesk'] text-xl font-semibold text-gray-900 mb-2">No products yet</h3>
            <p class="text-gray-600 mb-6 max-w-sm mx-auto">
                Your gallery is empty. Add your first product to get started.
            </p>
            <a href="{{ route('products.create') }}"
               class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 text-white text-sm font-medium transition-all duration-200 hover:bg-indigo-700 active:translate-y-px">
                <i class="ph ph-plus"></i> Add First Product
            </a>
        </div>
    @endif

</div>

{{-- Delete Confirm Modal --}}
<div id="pgmDeleteModal" class="fixed inset-0 bg-black/50 items-center justify-center z-50 hidden">
    <div class="bg-white max-w-md w-full mx-4">
        <div class="p-6">
            <div class="w-12 h-12 bg-red-100 mx-auto mb-4 flex items-center justify-center">
                <i class="ph-fill ph-trash text-red-600 text-2xl"></i>
            </div>
            <h3 class="font-['Space_Grotesk'] text-xl font-semibold text-center text-gray-900 mb-2">
                Delete Product?
            </h3>
            <p class="text-gray-600 text-center text-sm">
                You're about to permanently delete <strong id="pgmDeleteTarget" class="text-gray-900"></strong>.
                All associated images will be removed from storage. This action cannot be undone.
            </p>
            <div class="flex gap-3 mt-6">
                <button type="button" class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 font-medium transition-colors duration-200 hover:bg-gray-50" data-modal-close>
                    Cancel
                </button>
                <button type="button" class="flex-1 px-4 py-2 bg-red-600 text-white font-medium transition-colors duration-200 hover:bg-red-700" id="pgmConfirmDelete">
                    <i class="ph ph-trash"></i> Yes, Delete
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Delete modal functionality
    const modal = document.getElementById('pgmDeleteModal');
    const deleteTarget = document.getElementById('pgmDeleteTarget');
    const confirmBtn = document.getElementById('pgmConfirmDelete');
    let currentFormId = null;

    document.querySelectorAll('[data-delete-form]').forEach(btn => {
        btn.addEventListener('click', () => {
            currentFormId = btn.getAttribute('data-delete-form');
            const productName = btn.getAttribute('data-product-name');
            deleteTarget.textContent = productName;
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        });
    });

    document.querySelectorAll('[data-modal-close]').forEach(btn => {
        btn.addEventListener('click', () => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            currentFormId = null;
        });
    });

    confirmBtn.addEventListener('click', () => {
        if (currentFormId) {
            document.getElementById(currentFormId).submit();
        }
    });

    modal.addEventListener('click', (e) => {
        if (e.target === modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            currentFormId = null;
        }
    });
</script>
@endpush
@endsection
