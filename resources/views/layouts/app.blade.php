<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Inventory') — {{ config('app.name', 'Noman Inventory') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    @stack('styles')
</head>
<body class="bg-slate-100 text-slate-800 antialiased">
    {{-- Top bar --}}
    <nav class="fixed top-0 right-0 left-0 z-50 h-16 bg-white border-b border-slate-200 md:left-64">
        <div class="flex items-center h-full px-4 gap-4">
            <button type="button" id="inventory-sidebar-toggle" class="p-2 rounded-lg text-slate-600 hover:bg-slate-100 md:hidden" aria-label="Toggle menu" aria-expanded="false">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
            <div class="flex-1 min-w-0">
                <nav class="flex items-center gap-1 text-sm text-slate-600" aria-label="Breadcrumb">
                    <a href="{{ route('inventory.dashboard') }}" class="hover:text-slate-900">Inventory</a>
                    @hasSection('breadcrumbs')
                        @yield('breadcrumbs')
                    @else
                        <span class="text-slate-400">/</span>
                        <span class="text-slate-900 truncate">@yield('title', 'Dashboard')</span>
                    @endif
                </nav>
            </div>
        </div>
    </nav>

    @include('noman-inventory::layouts.partials.sidebar')

    <main class="pt-16 md:pl-64 min-h-screen">
        <div class="p-4 md:p-6">
            @if(session('success'))
                <div class="mb-4 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3" role="alert">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="mb-4 rounded-lg bg-red-50 border border-red-200 text-red-800 px-4 py-3" role="alert">{{ session('error') }}</div>
            @endif
            @if(isset($errors) && $errors->any())
                <div class="mb-4 rounded-lg bg-red-50 border border-red-200 text-red-800 px-4 py-3" role="alert">
                    <ul class="list-disc pl-5 space-y-1">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            @yield('content')
        </div>
    </main>

    <script>
        (function() {
            var toggle = document.getElementById('inventory-sidebar-toggle');
            var sidebar = document.getElementById('inventory-sidebar');
            var backdrop = document.getElementById('inventory-sidebar-backdrop');
            if (toggle && sidebar && backdrop) {
                toggle.addEventListener('click', function() {
                    var open = sidebar.classList.toggle('translate-x-0');
                    sidebar.classList.toggle('-translate-x-full', !open);
                    backdrop.classList.toggle('hidden', !open);
                    toggle.setAttribute('aria-expanded', open);
                });
                backdrop.addEventListener('click', function() {
                    sidebar.classList.add('-translate-x-full');
                    sidebar.classList.remove('translate-x-0');
                    backdrop.classList.add('hidden');
                    toggle.setAttribute('aria-expanded', 'false');
                });
            }
        })();
    </script>
    @stack('scripts')
</body>
</html>
