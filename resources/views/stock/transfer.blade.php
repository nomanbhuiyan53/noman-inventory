@extends('noman-inventory::layouts.app')

@section('title', 'Transfer Stock')

@section('breadcrumbs')
    <span class="text-slate-400">/</span>
    <span class="text-slate-900">Transfer Stock</span>
@stop

@section('content')
<div class="space-y-6">
    <header>
        <h1 class="text-2xl font-semibold text-slate-900">Transfer Stock</h1>
        <p class="mt-1 text-sm text-slate-600">Move stock from one warehouse to another.</p>
    </header>

    <div class="bg-white rounded-lg shadow p-6 max-w-2xl">
        <form action="{{ route('inventory.stock.transfer.submit') }}" method="post" class="space-y-4">
            @csrf
            <div>
                <label for="item_id" class="block text-sm font-medium text-slate-700 mb-1">Item *</label>
                <select name="item_id" id="item_id" required class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                    <option value="">— Select item —</option>
                    @foreach($items as $i)
                        <option value="{{ $i->id }}" {{ old('item_id') == $i->id ? 'selected' : '' }}>{{ $i->code }} — {{ $i->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="quantity" class="block text-sm font-medium text-slate-700 mb-1">Quantity *</label>
                <input type="number" name="quantity" id="quantity" value="{{ old('quantity', 1) }}" step="any" min="0.0001" required class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
            </div>
            <div>
                <label for="from_warehouse_id" class="block text-sm font-medium text-slate-700 mb-1">From warehouse *</label>
                <select name="from_warehouse_id" id="from_warehouse_id" required class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                    <option value="">— Select warehouse —</option>
                    @foreach($warehouses as $w)
                        <option value="{{ $w->id }}" {{ old('from_warehouse_id') == $w->id ? 'selected' : '' }}>{{ $w->code }} — {{ $w->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="to_warehouse_id" class="block text-sm font-medium text-slate-700 mb-1">To warehouse *</label>
                <select name="to_warehouse_id" id="to_warehouse_id" required class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                    <option value="">— Select warehouse —</option>
                    @foreach($warehouses as $w)
                        <option value="{{ $w->id }}" {{ old('to_warehouse_id') == $w->id ? 'selected' : '' }}>{{ $w->code }} — {{ $w->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="reference_doc_number" class="block text-sm font-medium text-slate-700 mb-1">Reference document number</label>
                <input type="text" name="reference_doc_number" id="reference_doc_number" value="{{ old('reference_doc_number') }}" class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
            </div>
            <div class="flex flex-wrap gap-2 pt-2">
                <button type="submit" class="inline-flex items-center px-4 py-2 rounded-lg bg-blue-600 text-white text-sm font-medium hover:bg-blue-700">Transfer Stock</button>
                <a href="{{ route('inventory.dashboard') }}" class="inline-flex items-center px-4 py-2 rounded-lg border border-slate-300 bg-white text-slate-700 text-sm font-medium hover:bg-slate-50">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
