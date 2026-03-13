@extends('noman-inventory::layouts.app')

@section('title', 'Stock Counts')

@section('breadcrumbs')
    <span class="text-slate-400">/</span>
    <span class="text-slate-900">Stock Counts</span>
@stop

@section('content')
<div class="space-y-6">
    <header class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900">Stock Counts</h1>
            <p class="mt-1 text-sm text-slate-600">Physical count sessions by warehouse.</p>
        </div>
        <a href="{{ route('inventory.stock-counts.start') }}" class="inline-flex items-center px-4 py-2 rounded-lg bg-blue-600 text-white text-sm font-medium hover:bg-blue-700">Start Stock Count</a>
    </header>

    <div class="bg-white rounded-lg shadow p-6">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead>
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Warehouse</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Count date</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase tracking-wider">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse($sessions as $s)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3 text-sm text-slate-900">{{ $s->warehouse->name ?? $s->warehouse_id }}</td>
                            <td class="px-4 py-3 text-sm text-slate-600">{{ $s->count_date ? \Carbon\Carbon::parse($s->count_date)->format('Y-m-d') : '—' }}</td>
                            <td class="px-4 py-3 text-sm text-slate-600">{{ $s->status ?? '—' }}</td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('inventory.stock-counts.show', $s->id) }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-12 text-center text-slate-500">
                                <p class="mb-2">No stock count sessions found.</p>
                                <a href="{{ route('inventory.stock-counts.start') }}" class="inline-flex items-center px-4 py-2 rounded-lg bg-blue-600 text-white text-sm font-medium hover:bg-blue-700">Start first count</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($sessions->hasPages())
            <div class="mt-4">{{ $sessions->withQueryString()->links() }}</div>
        @endif
    </div>
</div>
@endsection
