@extends('noman-inventory::layouts.app')

@section('title', 'Reports')

@section('content')
<div class="inventory-card">
    <h1>Reports</h1>
    <ul style="list-style:none; padding:0;">
        <li style="margin-bottom:0.5rem;"><a href="{{ route('inventory.reports.stock-on-hand') }}" class="btn btn-secondary">Stock on Hand</a></li>
        <li style="margin-bottom:0.5rem;"><a href="{{ route('inventory.reports.stock-by-location') }}" class="btn btn-secondary">Stock by Location</a></li>
        <li style="margin-bottom:0.5rem;"><a href="{{ route('inventory.reports.stock-ledger') }}" class="btn btn-secondary">Stock Ledger</a></li>
        <li style="margin-bottom:0.5rem;"><a href="{{ route('inventory.reports.batch-expiry') }}" class="btn btn-secondary">Batch Expiry</a></li>
        <li style="margin-bottom:0.5rem;"><a href="{{ route('inventory.reports.reservations') }}" class="btn btn-secondary">Reservation Status</a></li>
    </ul>
</div>
@endsection
