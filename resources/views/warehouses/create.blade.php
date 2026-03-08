@extends('noman-inventory::layouts.app')

@section('title', 'Add Warehouse')

@section('content')
<div class="inventory-card">
    <h1>Add Warehouse</h1>
    <form action="{{ route('inventory.warehouses.store') }}" method="post">
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
            <label for="address">Address</label>
            <textarea name="address" id="address" rows="2">{{ old('address') }}</textarea>
        </div>
        <div class="form-group">
            <label><input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}> Active</label>
        </div>
        <div class="flex">
            <button type="submit" class="btn btn-primary">Create Warehouse</button>
            <a href="{{ route('inventory.warehouses.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
