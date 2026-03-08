@extends('noman-inventory::layouts.app')

@section('title', 'Stock on Hand')

@section('content')
<div class="inventory-card">
    <h1>Stock on Hand</h1>
    <form method="get" class="form-group" style="max-width:400px; margin-bottom:1rem;">
        <label for="warehouse_id">Warehouse</label>
        <select name="warehouse_id" id="warehouse_id">
            <option value="">All</option>
            @foreach($warehouses as $w)
                <option value="{{ $w->id }}" {{ request('warehouse_id') == $w->id ? 'selected' : '' }}>{{ $w->code }} — {{ $w->name }}</option>
            @endforeach
        </select>
        <button type="submit" class="btn btn-secondary" style="margin-top:0.5rem;">Apply</button>
    </form>
    <table class="inventory-table">
        <thead>
            <tr>
                <th>Item</th>
                <th>Warehouse</th>
                <th>Quantity on hand</th>
                <th>Reserved</th>
                <th>Available</th>
            </tr>
        </thead>
        <tbody>
            @forelse($results as $r)
                <tr>
                    <td>{{ $r->itemCode ?? $r->itemId }} — {{ $r->itemName ?? '—' }}</td>
                    <td>{{ $r->warehouseName ?? $r->warehouseId ?? '—' }}</td>
                    <td>{{ $r->quantityOnHand ?? 0 }}</td>
                    <td>{{ $r->quantityReserved ?? 0 }}</td>
                    <td>{{ $r->quantityAvailable ?? 0 }}</td>
                </tr>
            @empty
                <tr><td colspan="5">No data.</td></tr>
            @endforelse
        </tbody>
    </table>
    <div class="flex" style="margin-top:1rem;">
        <a href="{{ route('inventory.reports.index') }}" class="btn btn-secondary">Back to Reports</a>
    </div>
</div>
@endsection
