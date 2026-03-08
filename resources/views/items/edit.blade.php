@extends('noman-inventory::layouts.app')

@section('title', 'Edit Item')

@section('content')
<div class="inventory-card">
    <h1>Edit Item</h1>
    <form action="{{ route('inventory.items.update', $item->id) }}" method="post">
        @csrf
        @method('PUT')
        <div class="form-group">
            <label for="name">Name *</label>
            <input type="text" name="name" id="name" value="{{ old('name', $item->name) }}" required>
        </div>
        <div class="form-group">
            <label for="code">Code *</label>
            <input type="text" name="code" id="code" value="{{ old('code', $item->code) }}" required>
        </div>
        <div class="form-group">
            <label for="sku">SKU</label>
            <input type="text" name="sku" id="sku" value="{{ old('sku', $item->sku) }}">
        </div>
        <div class="form-group">
            <label for="barcode">Barcode</label>
            <input type="text" name="barcode" id="barcode" value="{{ old('barcode', $item->barcode) }}">
        </div>
        <div class="form-group">
            <label><input type="checkbox" name="is_active" value="1" {{ old('is_active', $item->is_active) ? 'checked' : '' }}> Active</label>
        </div>
        <div class="form-group">
            <label><input type="checkbox" name="is_stockable" value="1" {{ old('is_stockable', $item->is_stockable) ? 'checked' : '' }}> Stockable</label>
        </div>
        <div class="flex">
            <button type="submit" class="btn btn-primary">Update Item</button>
            <a href="{{ route('inventory.items.show', $item->id) }}" class="btn btn-secondary">View</a>
            <a href="{{ route('inventory.items.index') }}" class="btn btn-secondary">Back to List</a>
        </div>
    </form>
</div>
@endsection
