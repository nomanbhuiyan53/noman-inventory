@extends('noman-inventory::layouts.app')

@section('title', 'Start Stock Count')

@section('breadcrumbs')
    <span class="text-slate-400">/</span>
    <a href="{{ route('inventory.stock-counts.index') }}" class="hover:text-slate-900">Stock Counts</a>
    <span class="text-slate-400">/</span>
    <span class="text-slate-900">Start</span>
@stop

@section('content')
<div class="space-y-6">
    <header>
        <h1 class="text-2xl font-semibold text-slate-900">Start Stock Count</h1>
        <p class="mt-1 text-sm text-slate-600">Create a new physical count session for a warehouse.</p>
    </header>

    <div class="bg-white rounded-lg shadow p-6 max-w-2xl">
        <form action="{{ route('inventory.stock-counts.start.submit') }}" method="post" class="space-y-4">
            @csrf
            <div>
                <label for="warehouse_id" class="block text-sm font-medium text-slate-700 mb-1">Warehouse *</label>
                <select name="warehouse_id" id="warehouse_id" required class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                    <option value="">— Select warehouse —</option>
                    @foreach($warehouses as $w)
                        <option value="{{ $w->id }}" {{ old('warehouse_id') == $w->id ? 'selected' : '' }}>{{ $w->code }} — {{ $w->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="count_date" class="block text-sm font-medium text-slate-700 mb-1">Count date *</label>
                <input type="date" name="count_date" id="count_date" value="{{ old('count_date', date('Y-m-d')) }}" required class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
            </div>
            <div class="flex flex-wrap gap-2 pt-2">
                <button type="submit" class="inline-flex items-center px-4 py-2 rounded-lg bg-blue-600 text-white text-sm font-medium hover:bg-blue-700">Start Count</button>
                <a href="{{ route('inventory.stock-counts.index') }}" class="inline-flex items-center px-4 py-2 rounded-lg border border-slate-300 bg-white text-slate-700 text-sm font-medium hover:bg-slate-50">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
