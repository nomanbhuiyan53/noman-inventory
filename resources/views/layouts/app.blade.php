<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Inventory') — {{ config('app.name', 'Noman Inventory') }}</title>
    @stack('styles')
    <style>
        * { box-sizing: border-box; }
        body { font-family: system-ui, -apple-system, sans-serif; margin: 0; padding: 0; background: #f5f5f5; }
        .inventory-nav { background: #1a1a2e; color: #eee; padding: 0.75rem 1.5rem; display: flex; gap: 1.5rem; align-items: center; flex-wrap: wrap; }
        .inventory-nav a { color: #eee; text-decoration: none; }
        .inventory-nav a:hover { text-decoration: underline; }
        .inventory-container { max-width: 1200px; margin: 0 auto; padding: 1.5rem; }
        .inventory-card { background: #fff; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); padding: 1.5rem; margin-bottom: 1.5rem; }
        .inventory-card h1, .inventory-card h2 { margin-top: 0; }
        table.inventory-table { width: 100%; border-collapse: collapse; }
        table.inventory-table th, table.inventory-table td { padding: 0.5rem 0.75rem; text-align: left; border-bottom: 1px solid #eee; }
        table.inventory-table th { background: #f8f8f8; font-weight: 600; }
        .btn { display: inline-block; padding: 0.5rem 1rem; border-radius: 6px; text-decoration: none; font-size: 0.875rem; cursor: pointer; border: none; }
        .btn-primary { background: #2563eb; color: #fff; }
        .btn-primary:hover { background: #1d4ed8; }
        .btn-secondary { background: #e5e7eb; color: #374151; }
        .btn-secondary:hover { background: #d1d5db; }
        .btn-danger { background: #dc2626; color: #fff; }
        .btn-danger:hover { background: #b91c1c; }
        .form-group { margin-bottom: 1rem; }
        .form-group label { display: block; margin-bottom: 0.25rem; font-weight: 500; }
        .form-group input, .form-group select, .form-group textarea { width: 100%; max-width: 400px; padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 6px; }
        .text-sm { font-size: 0.875rem; color: #6b7280; }
        .alert { padding: 0.75rem 1rem; border-radius: 6px; margin-bottom: 1rem; }
        .alert-success { background: #d1fae5; color: #065f46; }
        .alert-error { background: #fee2e2; color: #991b1b; }
        .flex { display: flex; gap: 0.5rem; flex-wrap: wrap; }
    </style>
</head>
<body>
    <nav class="inventory-nav">
        <a href="{{ route('inventory.dashboard') }}">Inventory</a>
        <a href="{{ route('inventory.items.index') }}">Items</a>
        <a href="{{ route('inventory.warehouses.index') }}">Warehouses</a>
        <a href="{{ route('inventory.stock.receive') }}">Receive</a>
        <a href="{{ route('inventory.stock.issue') }}">Issue</a>
        <a href="{{ route('inventory.stock.transfer') }}">Transfer</a>
        <a href="{{ route('inventory.documents.index') }}">Documents</a>
        <a href="{{ route('inventory.stock-counts.index') }}">Stock Counts</a>
        <a href="{{ route('inventory.reports.index') }}">Reports</a>
    </nav>

    <main class="inventory-container">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-error">{{ session('error') }}</div>
        @endif
        @if($errors->any())
            <div class="alert alert-error">
                <ul class="mb-0" style="margin:0; padding-left:1.25rem;">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        @yield('content')
    </main>

    @stack('scripts')
</body>
</html>
