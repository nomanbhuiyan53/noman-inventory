@extends('noman-inventory::layouts.app')

@section('title', 'Transfer Stock')

@section('content')
<div class="inventory-card">
    <h1>Transfer Stock</h1>
    <form action="{{ route('inventory.stock.transfer.submit') }}" method="post">
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
            <label for="quantity">Quantity *</label>
            <input type="number" name="quantity" id="quantity" value="{{ old('quantity', 1) }}" step="any" min="0.0001" required>
        </div>
        <div class="form-group">
            <label for="from_warehouse_id">From warehouse *</label>
            <select name="from_warehouse_id" id="from_warehouse_id" required>
                <option value="">— Select warehouse —</option>
                @foreach($warehouses as $w)
                    <option value="{{ $w->id }}" {{ old('from_warehouse_id') == $w->id ? 'selected' : '' }}>{{ $w->code }} — {{ $w->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label for="to_warehouse_id">To warehouse *</label>
            <select name="to_warehouse_id" id="to_warehouse_id" required>
                <option value="">— Select warehouse —</option>
                @foreach($warehouses as $w)
                    <option value="{{ $w->id }}" {{ old('to_warehouse_id') == $w->id ? 'selected' : '' }}>{{ $w->code }} — {{ $w->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label for="reference_doc_number">Reference document number</label>
            <input type="text" name="reference_doc_number" id="reference_doc_number" value="{{ old('reference_doc_number') }}">
        </div>
        <div class="flex">
            <button type="submit" class="btn btn-primary">Transfer Stock</button>
            <a href="{{ route('inventory.dashboard') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
