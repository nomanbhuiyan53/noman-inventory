@extends('noman-inventory::layouts.app')

@section('title', 'Adjust Stock')

@section('content')
<div class="inventory-card">
    <h1>Adjust Stock</h1>
    <p class="text-sm">Use positive quantity to add stock (e.g. found), negative to reduce (e.g. damage, loss).</p>
    <form action="{{ route('inventory.stock.adjust.submit') }}" method="post">
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
            <label for="quantity">Quantity * (positive = in, negative = out)</label>
            <input type="number" name="quantity" id="quantity" value="{{ old('quantity') }}" step="any" required>
        </div>
        <div class="form-group">
            <label for="reason">Reason</label>
            <input type="text" name="reason" id="reason" value="{{ old('reason') }}" placeholder="e.g. Damage, count correction">
        </div>
        <div class="flex">
            <button type="submit" class="btn btn-primary">Adjust Stock</button>
            <a href="{{ route('inventory.dashboard') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
