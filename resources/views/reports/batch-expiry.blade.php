@extends('noman-inventory::layouts.app')

@section('title', 'Batch Expiry')

@section('content')
<div class="inventory-card">
    <h1>Batch Expiry</h1>
    <table class="inventory-table">
        <thead>
            <tr>
                <th>Item</th>
                <th>Batch</th>
                <th>Expiry date</th>
                <th>Quantity</th>
                <th>Days until expiry</th>
            </tr>
        </thead>
        <tbody>
            @forelse($results ?? [] as $r)
                <tr>
                    <td>{{ $r->itemName ?? $r->itemId ?? '—' }}</td>
                    <td>{{ $r->batchCode ?? '—' }}</td>
                    <td>{{ $r->expiryDate ?? '—' }}</td>
                    <td>{{ $r->quantity ?? 0 }}</td>
                    <td>{{ $r->daysUntilExpiry ?? '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="5">No data.</td></tr>
            @endforelse
        </tbody>
    </table>
    <a href="{{ route('inventory.reports.index') }}" class="btn btn-secondary">Back to Reports</a>
</div>
@endsection
