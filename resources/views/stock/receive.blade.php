@extends('noman-inventory::layouts.app')

@section('title', 'Receive Stock')

@section('breadcrumbs')
    <span class="text-slate-400">/</span>
    <span class="text-slate-900">Receive Stock</span>
@stop

@section('content')
<div class="space-y-6">
    <header>
        <h1 class="text-2xl font-semibold text-slate-900">Receive Stock</h1>
        <p class="mt-1 text-sm text-slate-600">Record stock received into a warehouse.</p>
    </header>

    <div class="bg-white rounded-lg shadow p-6 max-w-2xl">
        <form action="{{ route('inventory.stock.receive.submit') }}" method="post" class="space-y-4">
            @csrf
            <div>
                <label for="item_id" class="block text-sm font-medium text-slate-700 mb-1">Item *</label>
                <select name="item_id" id="item_id" required class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                    <option value="">— Select item —</option>
                    @foreach($items as $i)
                        <option value="{{ $i->id }}" {{ old('item_id', request('item_id')) == $i->id ? 'selected' : '' }}>{{ $i->code }} — {{ $i->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="warehouse_id" class="block text-sm font-medium text-slate-700 mb-1">Warehouse *</label>
                <select name="warehouse_id" id="warehouse_id" required class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                    <option value="">— Select warehouse —</option>
                    @foreach($warehouses as $w)
                        <option value="{{ $w->id }}" {{ old('warehouse_id', request('warehouse_id')) == $w->id ? 'selected' : '' }}>{{ $w->code }} — {{ $w->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="quantity" class="block text-sm font-medium text-slate-700 mb-1">Quantity *</label>
                <input type="number" name="quantity" id="quantity" value="{{ old('quantity', 1) }}" step="any" min="0.0001" required class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
            </div>
            <div>
                <label for="unit_cost" class="block text-sm font-medium text-slate-700 mb-1">Unit cost</label>
                <input type="number" name="unit_cost" id="unit_cost" value="{{ old('unit_cost', 0) }}" step="0.01" min="0" class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
            </div>
            <div>
                <label for="batch_code" class="block text-sm font-medium text-slate-700 mb-1">Batch / Lot code</label>
                <input type="text" name="batch_code" id="batch_code" value="{{ old('batch_code') }}" class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
            </div>
            <div>
                <label for="expiry_date" class="block text-sm font-medium text-slate-700 mb-1">Expiry date</label>
                <input type="date" name="expiry_date" id="expiry_date" value="{{ old('expiry_date') }}" class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
            </div>
            <div>
                <label for="reference_doc_number" class="block text-sm font-medium text-slate-700 mb-1">Reference document number</label>
                <input type="text" name="reference_doc_number" id="reference_doc_number" value="{{ old('reference_doc_number') }}" class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
            </div>
            <div class="flex flex-wrap gap-2 pt-2">
                <button type="submit" class="inline-flex items-center px-4 py-2 rounded-lg bg-blue-600 text-white text-sm font-medium hover:bg-blue-700">Receive Stock</button>
                <a href="{{ route('inventory.dashboard') }}" class="inline-flex items-center px-4 py-2 rounded-lg border border-slate-300 bg-white text-slate-700 text-sm font-medium hover:bg-slate-50">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
