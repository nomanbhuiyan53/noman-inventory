@extends('noman-inventory::layouts.app')

@section('title', 'Stock Count')

@section('content')
<div class="inventory-card">
    <h1>Stock Count</h1>
    <p class="text-sm">Warehouse: {{ $session->warehouse->name ?? $session->warehouse_id }} · Date: {{ $session->count_date ? \Carbon\Carbon::parse($session->count_date)->format('Y-m-d') : '—' }}</p>
    <div class="flex" style="margin-bottom:1rem;">
        <a href="{{ route('inventory.stock-counts.index') }}" class="btn btn-secondary">Back to List</a>
    </div>
    <table class="inventory-table">
        <thead>
            <tr>
                <th>Item</th>
                <th>Expected</th>
                <th>Counted</th>
                <th>Variance</th>
            </tr>
        </thead>
        <tbody>
            @foreach($session->entries ?? [] as $e)
                <tr>
                    <td>{{ $e->item->name ?? $e->item_id }}</td>
                    <td>{{ $e->expected_quantity ?? 0 }}</td>
                    <td>{{ $e->counted_quantity ?? '—' }}</td>
                    <td>{{ $e->variance ?? '—' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
