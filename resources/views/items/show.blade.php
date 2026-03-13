@extends('noman-inventory::layouts.app')

@section('title', $item->name)

@section('breadcrumbs')
    <span class="text-slate-400">/</span>
    <a href="{{ route('inventory.items.index') }}" class="hover:text-slate-900">Items</a>
    <span class="text-slate-400">/</span>
    <span class="text-slate-900 truncate">{{ $item->name }}</span>
@stop

@section('content')
<div class="space-y-6">
    <header class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900">{{ $item->name }}</h1>
            <p class="mt-1 text-sm text-slate-600">Code: {{ $item->code }} · SKU: {{ $item->sku ?? '—' }}</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('inventory.items.edit', $item->id) }}" class="inline-flex items-center px-4 py-2 rounded-lg bg-blue-600 text-white text-sm font-medium hover:bg-blue-700">Edit</a>
            <a href="{{ route('inventory.stock.receive') }}?item_id={{ $item->id }}" class="inline-flex items-center px-4 py-2 rounded-lg border border-slate-300 bg-white text-slate-700 text-sm font-medium hover:bg-slate-50">Receive Stock</a>
            <a href="{{ route('inventory.items.index') }}" class="inline-flex items-center px-4 py-2 rounded-lg border border-slate-300 bg-white text-slate-700 text-sm font-medium hover:bg-slate-50">Back to List</a>
        </div>
    </header>

    <div class="bg-white rounded-lg shadow p-6 max-w-2xl">
        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div class="py-2 border-b border-slate-100"><dt class="text-xs font-medium text-slate-500 uppercase">Code</dt><dd class="mt-0.5 text-slate-900">{{ $item->code }}</dd></div>
            <div class="py-2 border-b border-slate-100"><dt class="text-xs font-medium text-slate-500 uppercase">Name</dt><dd class="mt-0.5 text-slate-900">{{ $item->name }}</dd></div>
            <div class="py-2 border-b border-slate-100"><dt class="text-xs font-medium text-slate-500 uppercase">SKU</dt><dd class="mt-0.5 text-slate-600">{{ $item->sku ?? '—' }}</dd></div>
            <div class="py-2 border-b border-slate-100"><dt class="text-xs font-medium text-slate-500 uppercase">Barcode</dt><dd class="mt-0.5 text-slate-600">{{ $item->barcode ?? '—' }}</dd></div>
            <div class="py-2 border-b border-slate-100"><dt class="text-xs font-medium text-slate-500 uppercase">Active</dt><dd class="mt-0.5 text-slate-900">{{ $item->is_active ? 'Yes' : 'No' }}</dd></div>
            <div class="py-2 border-b border-slate-100"><dt class="text-xs font-medium text-slate-500 uppercase">Stockable</dt><dd class="mt-0.5 text-slate-900">{{ $item->is_stockable ? 'Yes' : 'No' }}</dd></div>
        </dl>
    </div>
</div>
@endsection
