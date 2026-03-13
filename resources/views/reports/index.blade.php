@extends('noman-inventory::layouts.app')

@section('title', 'Reports')

@section('breadcrumbs')
    <span class="text-slate-400">/</span>
    <span class="text-slate-900">Reports</span>
@stop

@section('content')
<div class="space-y-6">
    <header>
        <h1 class="text-2xl font-semibold text-slate-900">Reports</h1>
        <p class="mt-1 text-sm text-slate-600">View stock balances, ledger, batch expiry, and reservations.</p>
    </header>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <a href="{{ route('inventory.reports.stock-on-hand') }}" class="block rounded-lg border border-slate-200 bg-white p-6 shadow hover:border-slate-300 hover:shadow-md transition-all">
            <h3 class="font-semibold text-slate-900">Stock on Hand</h3>
            <p class="mt-1 text-sm text-slate-600">Current quantity on hand by item and warehouse, with reserved and available.</p>
            <span class="mt-3 inline-block text-sm text-blue-600 font-medium">View report →</span>
        </a>
        <a href="{{ route('inventory.reports.stock-by-location') }}" class="block rounded-lg border border-slate-200 bg-white p-6 shadow hover:border-slate-300 hover:shadow-md transition-all">
            <h3 class="font-semibold text-slate-900">Stock by Location</h3>
            <p class="mt-1 text-sm text-slate-600">Quantities broken down by storage location within warehouses.</p>
            <span class="mt-3 inline-block text-sm text-blue-600 font-medium">View report →</span>
        </a>
        <a href="{{ route('inventory.reports.stock-ledger') }}" class="block rounded-lg border border-slate-200 bg-white p-6 shadow hover:border-slate-300 hover:shadow-md transition-all">
            <h3 class="font-semibold text-slate-900">Stock Ledger</h3>
            <p class="mt-1 text-sm text-slate-600">Movement history by date, item, type, and document.</p>
            <span class="mt-3 inline-block text-sm text-blue-600 font-medium">View report →</span>
        </a>
        <a href="{{ route('inventory.reports.batch-expiry') }}" class="block rounded-lg border border-slate-200 bg-white p-6 shadow hover:border-slate-300 hover:shadow-md transition-all">
            <h3 class="font-semibold text-slate-900">Batch Expiry</h3>
            <p class="mt-1 text-sm text-slate-600">Batch/lot quantities with expiry dates and days until expiry.</p>
            <span class="mt-3 inline-block text-sm text-blue-600 font-medium">View report →</span>
        </a>
        <a href="{{ route('inventory.reports.reservations') }}" class="block rounded-lg border border-slate-200 bg-white p-6 shadow hover:border-slate-300 hover:shadow-md transition-all">
            <h3 class="font-semibold text-slate-900">Reservation Status</h3>
            <p class="mt-1 text-sm text-slate-600">Reserved quantities by item, warehouse, reference, and expiry.</p>
            <span class="mt-3 inline-block text-sm text-blue-600 font-medium">View report →</span>
        </a>
    </div>
</div>
@endsection
