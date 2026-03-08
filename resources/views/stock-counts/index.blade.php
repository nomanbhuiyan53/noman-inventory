@extends('noman-inventory::layouts.app')

@section('title', 'Stock Counts')

@section('content')
<div class="inventory-card">
    <h1>Stock Counts</h1>
    <div class="flex" style="margin-bottom:1rem;">
        <a href="{{ route('inventory.stock-counts.start') }}" class="btn btn-primary">Start Stock Count</a>
    </div>
    <table class="inventory-table">
        <thead>
            <tr>
                <th>Warehouse</th>
                <th>Count date</th>
                <th>Status</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse($sessions as $s)
                <tr>
                    <td>{{ $s->warehouse->name ?? $s->warehouse_id }}</td>
                    <td>{{ $s->count_date ? \Carbon\Carbon::parse($s->count_date)->format('Y-m-d') : '—' }}</td>
                    <td>{{ $s->status ?? '—' }}</td>
                    <td>
                        <a href="{{ route('inventory.stock-counts.show', $s->id) }}" class="btn btn-secondary">View</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="4">No stock count sessions found.</td></tr>
            @endforelse
        </tbody>
    </table>
    {{ $sessions->withQueryString()->links() }}
</div>
@endsection
