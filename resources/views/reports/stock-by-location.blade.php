@extends('noman-inventory::layouts.app')

@section('title', 'Stock by Location')

@section('breadcrumbs')
    <span class="text-slate-400">/</span>
    <a href="{{ route('inventory.reports.index') }}" class="hover:text-slate-900">Reports</a>
    <span class="text-slate-400">/</span>
    <span class="text-slate-900">Stock by Location</span>
@stop

@section('content')
<div class="space-y-6">
    <header class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900">Stock by Location</h1>
            <p class="mt-1 text-sm text-slate-600">Quantities by storage location.</p>
        </div>
        <a href="{{ route('inventory.reports.index') }}" class="inline-flex items-center px-4 py-2 rounded-lg border border-slate-300 bg-white text-slate-700 text-sm font-medium hover:bg-slate-50">Back to Reports</a>
    </header>

    <div class="bg-white rounded-lg shadow p-6">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead>
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Item</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Location</th>
                        <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase tracking-wider">Quantity</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse($results ?? [] as $r)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3 text-sm text-slate-900">{{ $r->itemCode ?? $r->itemId ?? '—' }}</td>
                            <td class="px-4 py-3 text-sm text-slate-600">{{ $r->locationCode ?? $r->locationId ?? '—' }}</td>
                            <td class="px-4 py-3 text-sm text-slate-600 text-right">{{ $r->quantity ?? 0 }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="px-4 py-12 text-center text-slate-500">No data.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
