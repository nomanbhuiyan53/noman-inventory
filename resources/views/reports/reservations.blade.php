@extends('noman-inventory::layouts.app')

@section('title', 'Reservation Status')

@section('content')
<div class="inventory-card">
    <h1>Reservation Status</h1>
    <table class="inventory-table">
        <thead>
            <tr>
                <th>Item</th>
                <th>Warehouse</th>
                <th>Quantity</th>
                <th>Reference</th>
                <th>Expires</th>
            </tr>
        </thead>
        <tbody>
            @forelse($results ?? [] as $r)
                <tr>
                    <td>{{ $r->itemCode ?? $r->itemId ?? '—' }}</td>
                    <td>{{ $r->warehouseId ?? '—' }}</td>
                    <td>{{ $r->reservedQuantity ?? 0 }}</td>
                    <td>{{ $r->referenceType ?? '—' }} {{ $r->referenceId ?? '' }}</td>
                    <td>{{ $r->expiresAt ?? '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="5">No data.</td></tr>
            @endforelse
        </tbody>
    </table>
    <a href="{{ route('inventory.reports.index') }}" class="btn btn-secondary">Back to Reports</a>
</div>
@endsection
