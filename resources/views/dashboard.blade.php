@extends('noman-inventory::layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="inventory-card">
    <h1>Inventory Dashboard</h1>
    <p class="text-sm">Manage items, warehouses, stock movements, and reports.</p>
    <div class="flex" style="margin-top:1rem;">
        <a href="{{ route('inventory.items.index') }}" class="btn btn-primary">Items</a>
        <a href="{{ route('inventory.warehouses.index') }}" class="btn btn-secondary">Warehouses</a>
        <a href="{{ route('inventory.stock.receive') }}" class="btn btn-secondary">Receive Stock</a>
        <a href="{{ route('inventory.reports.stock-on-hand') }}" class="btn btn-secondary">Stock on Hand</a>
    </div>
</div>
@endsection
