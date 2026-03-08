@extends('noman-inventory::layouts.app')

@section('title', 'Add Item')

@section('content')
<div class="inventory-card">
    <h1>Add Item</h1>
    <form action="{{ route('inventory.items.store') }}" method="post">
        @csrf
        <div class="form-group">
            <label for="name">Name *</label>
            <input type="text" name="name" id="name" value="{{ old('name') }}" required>
        </div>
        <div class="form-group">
            <label for="code">Code *</label>
            <input type="text" name="code" id="code" value="{{ old('code') }}" required>
        </div>
        <div class="form-group">
            <label for="sku">SKU</label>
            <input type="text" name="sku" id="sku" value="{{ old('sku') }}">
        </div>
        <div class="form-group">
            <label for="barcode">Barcode</label>
            <input type="text" name="barcode" id="barcode" value="{{ old('barcode') }}">
        </div>
        <div class="form-group">
            <label><input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}> Active</label>
        </div>
        <div class="form-group">
            <label><input type="checkbox" name="is_stockable" value="1" {{ old('is_stockable', true) ? 'checked' : '' }}> Stockable</label>
        </div>
        <div class="flex">
            <button type="submit" class="btn btn-primary">Create Item</button>
            <a href="{{ route('inventory.items.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
