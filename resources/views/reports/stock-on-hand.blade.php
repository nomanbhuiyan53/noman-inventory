@extends('noman-inventory::layouts.app')

@section('title', 'Stock on Hand')

@section('breadcrumbs')
    <span class="text-slate-400">/</span>
    <a href="{{ route('inventory.reports.index') }}" class="hover:text-slate-900">Reports</a>
    <span class="text-slate-400">/</span>
    <span class="text-slate-900">Stock on Hand</span>
@stop

@section('content')
<div class="space-y-6">
    <header class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900">Stock on Hand</h1>
            <p class="mt-1 text-sm text-slate-600">Current quantity by item and warehouse.</p>
        </div>
        <a href="{{ route('inventory.reports.index') }}" class="inline-flex items-center px-4 py-2 rounded-lg border border-slate-300 bg-white text-slate-700 text-sm font-medium hover:bg-slate-50">Back to Reports</a>
    </header>

    <div class="bg-white rounded-lg shadow p-6">
        <form method="get" class="mb-4 flex flex-wrap items-end gap-3">
            <div>
                <label for="warehouse_id" class="block text-sm font-medium text-slate-700 mb-1">Warehouse</label>
                <select name="warehouse_id" id="warehouse_id" class="block rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                    <option value="">All</option>
                    @foreach($warehouses as $w)
                        <option value="{{ $w->id }}" {{ request('warehouse_id') == $w->id ? 'selected' : '' }}>{{ $w->code }} — {{ $w->name }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="inline-flex items-center px-4 py-2 rounded-lg border border-slate-300 bg-white text-slate-700 text-sm font-medium hover:bg-slate-50">Apply</button>
        </form>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead>
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Item</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Warehouse</th>
                        <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase tracking-wider">Quantity on hand</th>
                        <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase tracking-wider">Reserved</th>
                        <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase tracking-wider">Available</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse($results as $r)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3 text-sm text-slate-900">{{ $r->itemCode ?? $r->itemId }} — {{ $r->itemName ?? '—' }}</td>
                            <td class="px-4 py-3 text-sm text-slate-600">{{ $r->warehouseName ?? $r->warehouseId ?? '—' }}</td>
                            <td class="px-4 py-3 text-sm text-slate-600 text-right">{{ $r->quantityOnHand ?? 0 }}</td>
                            <td class="px-4 py-3 text-sm text-slate-600 text-right">{{ $r->quantityReserved ?? 0 }}</td>
                            <td class="px-4 py-3 text-sm text-slate-600 text-right">{{ $r->quantityAvailable ?? 0 }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-12 text-center text-slate-500">No data.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
