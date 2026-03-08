@extends('noman-inventory::layouts.app')

@section('title', $item->name)

@section('content')
<div class="inventory-card">
    <h1>{{ $item->name }}</h1>
    <p class="text-sm">Code: {{ $item->code }} · SKU: {{ $item->sku ?? '—' }}</p>
    <div class="flex" style="margin-bottom:1rem;">
        <a href="{{ route('inventory.items.edit', $item->id) }}" class="btn btn-primary">Edit</a>
        <a href="{{ route('inventory.stock.receive') }}?item_id={{ $item->id }}" class="btn btn-secondary">Receive Stock</a>
        <a href="{{ route('inventory.items.index') }}" class="btn btn-secondary">Back to List</a>
    </div>
    <table class="inventory-table" style="max-width:500px;">
        <tr><th>Code</th><td>{{ $item->code }}</td></tr>
        <tr><th>Name</th><td>{{ $item->name }}</td></tr>
        <tr><th>SKU</th><td>{{ $item->sku ?? '—' }}</td></tr>
        <tr><th>Barcode</th><td>{{ $item->barcode ?? '—' }}</td></tr>
        <tr><th>Active</th><td>{{ $item->is_active ? 'Yes' : 'No' }}</td></tr>
        <tr><th>Stockable</th><td>{{ $item->is_stockable ? 'Yes' : 'No' }}</td></tr>
    </table>
</div>
@endsection
