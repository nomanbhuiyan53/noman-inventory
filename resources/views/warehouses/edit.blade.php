@extends('noman-inventory::layouts.app')

@section('title', 'Edit Warehouse')

@section('content')
<div class="inventory-card">
    <h1>Edit Warehouse</h1>
    <form action="{{ route('inventory.warehouses.update', $warehouse->id) }}" method="post">
        @csrf
        @method('PUT')
        <div class="form-group">
            <label for="name">Name *</label>
            <input type="text" name="name" id="name" value="{{ old('name', $warehouse->name) }}" required>
        </div>
        <div class="form-group">
            <label for="code">Code *</label>
            <input type="text" name="code" id="code" value="{{ old('code', $warehouse->code) }}" required>
        </div>
        <div class="form-group">
            <label for="address">Address</label>
            <textarea name="address" id="address" rows="2">{{ old('address', $warehouse->address) }}</textarea>
        </div>
        <div class="form-group">
            <label><input type="checkbox" name="is_active" value="1" {{ old('is_active', $warehouse->is_active) ? 'checked' : '' }}> Active</label>
        </div>
        <div class="flex">
            <button type="submit" class="btn btn-primary">Update Warehouse</button>
            <a href="{{ route('inventory.warehouses.show', $warehouse->id) }}" class="btn btn-secondary">View</a>
            <a href="{{ route('inventory.warehouses.index') }}" class="btn btn-secondary">Back to List</a>
        </div>
    </form>
</div>
@endsection
