@extends('noman-inventory::layouts.app')

@section('title', 'Receive Stock')

@section('content')
<div class="inventory-card">
    <h1>Receive Stock</h1>
    <form action="{{ route('inventory.stock.receive.submit') }}" method="post">
        @csrf
        <div class="form-group">
            <label for="item_id">Item *</label>
            <select name="item_id" id="item_id" required>
                <option value="">— Select item —</option>
                @foreach($items as $i)
                    <option value="{{ $i->id }}" {{ old('item_id', request('item_id')) == $i->id ? 'selected' : '' }}>{{ $i->code }} — {{ $i->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label for="warehouse_id">Warehouse *</label>
            <select name="warehouse_id" id="warehouse_id" required>
                <option value="">— Select warehouse —</option>
                @foreach($warehouses as $w)
                    <option value="{{ $w->id }}" {{ old('warehouse_id', request('warehouse_id')) == $w->id ? 'selected' : '' }}>{{ $w->code }} — {{ $w->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label for="quantity">Quantity *</label>
            <input type="number" name="quantity" id="quantity" value="{{ old('quantity', 1) }}" step="any" min="0.0001" required>
        </div>
        <div class="form-group">
            <label for="unit_cost">Unit cost</label>
            <input type="number" name="unit_cost" id="unit_cost" value="{{ old('unit_cost', 0) }}" step="0.01" min="0">
        </div>
        <div class="form-group">
            <label for="batch_code">Batch / Lot code</label>
            <input type="text" name="batch_code" id="batch_code" value="{{ old('batch_code') }}">
        </div>
        <div class="form-group">
            <label for="expiry_date">Expiry date</label>
            <input type="date" name="expiry_date" id="expiry_date" value="{{ old('expiry_date') }}">
        </div>
        <div class="form-group">
            <label for="reference_doc_number">Reference document number</label>
            <input type="text" name="reference_doc_number" id="reference_doc_number" value="{{ old('reference_doc_number') }}">
        </div>
        <div class="flex">
            <button type="submit" class="btn btn-primary">Receive Stock</button>
            <a href="{{ route('inventory.dashboard') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
