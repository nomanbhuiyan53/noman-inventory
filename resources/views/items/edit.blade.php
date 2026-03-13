@extends('noman-inventory::layouts.app')

@section('title', 'Edit Item')

@section('breadcrumbs')
    <span class="text-slate-400">/</span>
    <a href="{{ route('inventory.items.index') }}" class="hover:text-slate-900">Items</a>
    <span class="text-slate-400">/</span>
    <span class="text-slate-900">Edit</span>
@stop

@section('content')
<div class="space-y-6">
    <header>
        <h1 class="text-2xl font-semibold text-slate-900">Edit Item</h1>
    </header>

    <div class="bg-white rounded-lg shadow p-6 max-w-2xl">
        <form action="{{ route('inventory.items.update', $item->id) }}" method="post" class="space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label for="name" class="block text-sm font-medium text-slate-700 mb-1">Name *</label>
                <input type="text" name="name" id="name" value="{{ old('name', $item->name) }}" required class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
            </div>
            <div>
                <label for="code" class="block text-sm font-medium text-slate-700 mb-1">Code *</label>
                <input type="text" name="code" id="code" value="{{ old('code', $item->code) }}" required class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
            </div>
            <div>
                <label for="sku" class="block text-sm font-medium text-slate-700 mb-1">SKU</label>
                <input type="text" name="sku" id="sku" value="{{ old('sku', $item->sku) }}" class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
            </div>
            <div>
                <label for="barcode" class="block text-sm font-medium text-slate-700 mb-1">Barcode</label>
                <input type="text" name="barcode" id="barcode" value="{{ old('barcode', $item->barcode) }}" class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
            </div>
            <div class="flex items-center gap-4">
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $item->is_active) ? 'checked' : '' }} class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                    <span class="text-sm text-slate-700">Active</span>
                </label>
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="is_stockable" value="1" {{ old('is_stockable', $item->is_stockable) ? 'checked' : '' }} class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                    <span class="text-sm text-slate-700">Stockable</span>
                </label>
            </div>
            <div class="flex flex-wrap gap-2 pt-2">
                <button type="submit" class="inline-flex items-center px-4 py-2 rounded-lg bg-blue-600 text-white text-sm font-medium hover:bg-blue-700">Update Item</button>
                <a href="{{ route('inventory.items.show', $item->id) }}" class="inline-flex items-center px-4 py-2 rounded-lg border border-slate-300 bg-white text-slate-700 text-sm font-medium hover:bg-slate-50">View</a>
                <a href="{{ route('inventory.items.index') }}" class="inline-flex items-center px-4 py-2 rounded-lg border border-slate-300 bg-white text-slate-700 text-sm font-medium hover:bg-slate-50">Back to List</a>
            </div>
        </form>
    </div>
</div>
@endsection
