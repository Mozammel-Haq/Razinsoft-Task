<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Products') — Gallery Manager</title>

    {{-- Google Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700&family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">

    {{-- Phosphor Icons --}}
    <script src="https://unpkg.com/@phosphor-icons/web@2.1.1/src/index.js" defer></script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-white font-['Inter'] antialiased">

    {{-- Top Navigation --}}
    <header class="border-b border-gray-200 bg-white sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                {{-- Logo --}}
                <a href="{{ route('products.index') }}" class="flex items-center gap-2 group">
                    <div class="w-8 h-8 bg-indigo-600 flex items-center justify-center">
                        <i class="ph-fill ph-storefront text-white text-lg"></i>
                    </div>
                    <span class="font-['Space_Grotesk'] font-bold text-xl tracking-tight text-gray-900">
                        Gallery<span class="text-indigo-600">Manager</span>
                    </span>
                </a>

                {{-- Navigation --}}
                <nav class="hidden md:flex items-center gap-1">
                    <a href="{{ route('products.index') }}"
                       class="px-4 py-2 text-sm font-medium transition-colors duration-200
                       {{ request()->routeIs('products.index')
                          ? 'bg-indigo-50 text-indigo-600'
                          : 'text-gray-600 hover:text-indigo-600 hover:bg-gray-50' }}">
                        <i class="ph ph-squares-four mr-2"></i>All Products
                    </a>
                    <a href="{{ route('products.create') }}"
                       class="px-4 py-2 text-sm font-medium transition-colors duration-200
                       {{ request()->routeIs('products.create')
                          ? 'bg-indigo-50 text-indigo-600'
                          : 'text-gray-600 hover:text-indigo-600 hover:bg-gray-50' }}">
                        <i class="ph ph-plus-circle mr-2"></i>Add Product
                    </a>
                </nav>

                {{-- CTA Button --}}
                <a href="{{ route('products.create') }}"
                   class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white text-sm font-medium transition-all duration-200 hover:bg-indigo-700 active:translate-y-px">
                    <i class="ph ph-plus"></i>
                    <span class="hidden sm:inline">New Product</span>
                </a>
            </div>
        </div>
    </header>

        {{-- Flash Messages --}}
    @if(session('success'))
        <div class="fixed top-20 right-4 z-50 animate-slide-in" id="pgmToast">
            <div class="flex items-center gap-3 px-5 py-4 bg-emerald-500 text-white rounded-lg shadow-lg shadow-emerald-500/20">
                <i class="ph-fill ph-check-circle text-white text-xl flex-shrink-0"></i>
                <span class="text-sm font-medium">{{ session('success') }}</span>
                <button onclick="this.closest('#pgmToast').remove()" class="ml-2 text-white/80 hover:text-white transition-colors">
                    <i class="ph ph-x"></i>
                </button>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="fixed top-20 right-4 z-50 animate-slide-in" id="pgmToast">
            <div class="flex items-center gap-3 px-5 py-4 bg-red-500 text-white rounded-lg shadow-lg shadow-red-500/20">
                <i class="ph-fill ph-warning-circle text-white text-xl flex-shrink-0"></i>
                <span class="text-sm font-medium">{{ session('error') }}</span>
                <button onclick="this.closest('#pgmToast').remove()" class="ml-2 text-white/80 hover:text-white transition-colors">
                    <i class="ph ph-x"></i>
                </button>
            </div>
        </div>
    @endif

    {{-- Main Content --}}
    <main class="min-h-screen bg-gray-50">
        @yield('content')
    </main>

    {{-- Footer --}}
    <footer class="border-t border-gray-200 bg-white mt-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
                <span class="text-sm text-gray-500">
                    © {{ date('Y') }} Product Management — RazinSoft Technical Assessment
                </span>
                <span class="text-sm text-gray-400">
                    Built by Mozammel Haq with Laravel 12 & Tailwind CSS
                </span>
            </div>
        </div>
    </footer>

    @stack('scripts')

    {{-- Auto-dismiss toast --}}
    <script>
        const toast = document.getElementById('pgmToast');
        if (toast) setTimeout(() => toast.remove(), 4000);
    </script>

    <style>
        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        .animate-slide-in {
            animation: slideIn 0.3s ease-out;
        }
    </style>
</body>
</html>
