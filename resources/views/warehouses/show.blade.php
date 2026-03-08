@extends('noman-inventory::layouts.app')

@section('title', $warehouse->name)

@section('content')
<div class="inventory-card">
    <h1>{{ $warehouse->name }}</h1>
    <p class="text-sm">Code: {{ $warehouse->code }}</p>
    <div class="flex" style="margin-bottom:1rem;">
        <a href="{{ route('inventory.warehouses.edit', $warehouse->id) }}" class="btn btn-primary">Edit</a>
        <a href="{{ route('inventory.stock.receive') }}?warehouse_id={{ $warehouse->id }}" class="btn btn-secondary">Receive Stock</a>
        <a href="{{ route('inventory.warehouses.index') }}" class="btn btn-secondary">Back to List</a>
    </div>
    <table class="inventory-table" style="max-width:500px;">
        <tr><th>Code</th><td>{{ $warehouse->code }}</td></tr>
        <tr><th>Name</th><td>{{ $warehouse->name }}</td></tr>
        <tr><th>Address</th><td>{{ $warehouse->address ?? '—' }}</td></tr>
        <tr><th>Active</th><td>{{ $warehouse->is_active ? 'Yes' : 'No' }}</td></tr>
    </table>
</div>
@endsection
