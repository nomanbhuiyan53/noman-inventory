@extends('noman-inventory::layouts.app')

@section('title', 'Start Stock Count')

@section('content')
<div class="inventory-card">
    <h1>Start Stock Count</h1>
    <form action="{{ route('inventory.stock-counts.start.submit') }}" method="post">
        @csrf
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
            <label for="count_date">Count date *</label>
            <input type="date" name="count_date" id="count_date" value="{{ old('count_date', date('Y-m-d')) }}" required>
        </div>
        <div class="flex">
            <button type="submit" class="btn btn-primary">Start Count</button>
            <a href="{{ route('inventory.stock-counts.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
