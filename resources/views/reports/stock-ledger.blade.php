@extends('noman-inventory::layouts.app')

@section('title', 'Stock Ledger')

@section('content')
<div class="inventory-card">
    <h1>Stock Ledger</h1>
    <table class="inventory-table">
        <thead>
            <tr>
                <th>Date</th>
                <th>Item</th>
                <th>Type</th>
                <th>Quantity</th>
                <th>Document</th>
            </tr>
        </thead>
        <tbody>
            @forelse($results ?? [] as $r)
                <tr>
                    <td>{{ $r->postedAt ?? '—' }}</td>
                    <td>{{ $r->itemCode ?? $r->itemId ?? '—' }}</td>
                    <td>{{ $r->movementType ?? '—' }}</td>
                    <td>{{ $r->quantity ?? 0 }}</td>
                    <td>{{ $r->documentNumber ?? '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="5">No data.</td></tr>
            @endforelse
        </tbody>
    </table>
    <a href="{{ route('inventory.reports.index') }}" class="btn btn-secondary">Back to Reports</a>
</div>
@endsection
