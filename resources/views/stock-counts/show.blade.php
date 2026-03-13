@extends('noman-inventory::layouts.app')

@section('title', 'Stock Count')

@section('breadcrumbs')
    <span class="text-slate-400">/</span>
    <a href="{{ route('inventory.stock-counts.index') }}" class="hover:text-slate-900">Stock Counts</a>
    <span class="text-slate-400">/</span>
    <span class="text-slate-900 truncate">Session</span>
@stop

@section('content')
<div class="space-y-6">
    <header class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900">Stock Count</h1>
            <p class="mt-1 text-sm text-slate-600">Warehouse: {{ $session->warehouse->name ?? $session->warehouse_id }} · Date: {{ $session->count_date ? \Carbon\Carbon::parse($session->count_date)->format('Y-m-d') : '—' }}</p>
        </div>
        <a href="{{ route('inventory.stock-counts.index') }}" class="inline-flex items-center px-4 py-2 rounded-lg border border-slate-300 bg-white text-slate-700 text-sm font-medium hover:bg-slate-50">Back to List</a>
    </header>

    <div class="bg-white rounded-lg shadow p-6">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead>
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Item</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Expected</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Counted</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Variance</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @foreach($session->entries ?? [] as $e)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3 text-sm text-slate-900">{{ $e->item->name ?? $e->item_id }}</td>
                            <td class="px-4 py-3 text-sm text-slate-600">{{ $e->expected_quantity ?? 0 }}</td>
                            <td class="px-4 py-3 text-sm text-slate-600">{{ $e->counted_quantity ?? '—' }}</td>
                            <td class="px-4 py-3 text-sm text-slate-600">{{ $e->variance ?? '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
