@extends('noman-inventory::layouts.app')

@section('title', 'Issue Stock')

@section('content')
<div class="inventory-card">
    <h1>Issue Stock</h1>
    <form action="{{ route('inventory.stock.issue.submit') }}" method="post">
        @csrf
        <div class="form-group">
            <label for="item_id">Item *</label>
            <select name="item_id" id="item_id" required>
                <option value="">— Select item —</option>
                @foreach($items as $i)
                    <option value="{{ $i->id }}" {{ old('item_id') == $i->id ? 'selected' : '' }}>{{ $i->code }} — {{ $i->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label for="warehouse_id">Warehouse *</label>
            <select name="warehouse_id" id="warehouse_id" required>
                <option value="">— Select warehouse —</option>
                @foreach($warehouses as $w)
                    <option value="{{ $w->id }}" {{ old('warehouse_id') == $w->id ? 'selected' : '' }}>{{ $w->code }} — {{ $w->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label for="quantity">Quantity *</label>
            <input type="number" name="quantity" id="quantity" value="{{ old('quantity', 1) }}" step="any" min="0.0001" required>
        </div>
        <div class="form-group">
            <label for="movement_type">Movement type *</label>
            <select name="movement_type" id="movement_type" required>
                <option value="sale_out" {{ old('movement_type') == 'sale_out' ? 'selected' : '' }}>Sale out</option>
                <option value="consumption_out">Consumption out</option>
                <option value="wastage_out">Wastage out</option>
                <option value="transfer_out">Transfer out</option>
                <option value="return_out">Return out</option>
                <option value="expired_out">Expired out</option>
            </select>
        </div>
        <div class="form-group">
            <label for="reference_doc_number">Reference document number</label>
            <input type="text" name="reference_doc_number" id="reference_doc_number" value="{{ old('reference_doc_number') }}">
        </div>
        <div class="flex">
            <button type="submit" class="btn btn-primary">Issue Stock</button>
            <a href="{{ route('inventory.dashboard') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
