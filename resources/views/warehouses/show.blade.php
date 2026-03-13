@extends('noman-inventory::layouts.app')

@section('title', $warehouse->name)

@section('breadcrumbs')
    <span class="text-slate-400">/</span>
    <a href="{{ route('inventory.warehouses.index') }}" class="hover:text-slate-900">Warehouses</a>
    <span class="text-slate-400">/</span>
    <span class="text-slate-900 truncate">{{ $warehouse->name }}</span>
@stop

@section('content')
<div class="space-y-6">
    <header class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900">{{ $warehouse->name }}</h1>
            <p class="mt-1 text-sm text-slate-600">Code: {{ $warehouse->code }}</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('inventory.warehouses.edit', $warehouse->id) }}" class="inline-flex items-center px-4 py-2 rounded-lg bg-blue-600 text-white text-sm font-medium hover:bg-blue-700">Edit</a>
            <a href="{{ route('inventory.stock.receive') }}?warehouse_id={{ $warehouse->id }}" class="inline-flex items-center px-4 py-2 rounded-lg border border-slate-300 bg-white text-slate-700 text-sm font-medium hover:bg-slate-50">Receive Stock</a>
            <a href="{{ route('inventory.warehouses.index') }}" class="inline-flex items-center px-4 py-2 rounded-lg border border-slate-300 bg-white text-slate-700 text-sm font-medium hover:bg-slate-50">Back to List</a>
        </div>
    </header>

    <div class="bg-white rounded-lg shadow p-6 max-w-2xl">
        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div class="py-2 border-b border-slate-100"><dt class="text-xs font-medium text-slate-500 uppercase">Code</dt><dd class="mt-0.5 text-slate-900">{{ $warehouse->code }}</dd></div>
            <div class="py-2 border-b border-slate-100"><dt class="text-xs font-medium text-slate-500 uppercase">Name</dt><dd class="mt-0.5 text-slate-900">{{ $warehouse->name }}</dd></div>
            <div class="py-2 border-b border-slate-100 sm:col-span-2"><dt class="text-xs font-medium text-slate-500 uppercase">Address</dt><dd class="mt-0.5 text-slate-600">{{ $warehouse->address ?? '—' }}</dd></div>
            <div class="py-2 border-b border-slate-100"><dt class="text-xs font-medium text-slate-500 uppercase">Active</dt><dd class="mt-0.5 text-slate-900">{{ $warehouse->is_active ? 'Yes' : 'No' }}</dd></div>
        </dl>
    </div>
</div>
@endsection
