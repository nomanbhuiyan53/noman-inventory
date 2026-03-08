@extends('noman-inventory::layouts.app')

@section('title', 'Stock by Location')

@section('content')
<div class="inventory-card">
    <h1>Stock by Location</h1>
    <table class="inventory-table">
        <thead>
            <tr>
                <th>Item</th>
                <th>Location</th>
                <th>Quantity</th>
            </tr>
        </thead>
        <tbody>
            @forelse($results ?? [] as $r)
                <tr>
                    <td>{{ $r->itemCode ?? $r->itemId ?? '—' }}</td>
                    <td>{{ $r->locationCode ?? $r->locationId ?? '—' }}</td>
                    <td>{{ $r->quantity ?? 0 }}</td>
                </tr>
            @empty
                <tr><td colspan="3">No data.</td></tr>
            @endforelse
        </tbody>
    </table>
    <a href="{{ route('inventory.reports.index') }}" class="btn btn-secondary">Back to Reports</a>
</div>
@endsection
