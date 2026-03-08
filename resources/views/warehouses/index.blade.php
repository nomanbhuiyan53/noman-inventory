@extends('noman-inventory::layouts.app')

@section('title', 'Warehouses')

@section('content')
<div class="inventory-card">
    <h1>Warehouses</h1>
    <div class="flex" style="margin-bottom:1rem;">
        <a href="{{ route('inventory.warehouses.create') }}" class="btn btn-primary">Add Warehouse</a>
    </div>
    <table class="inventory-table">
        <thead>
            <tr>
                <th>Code</th>
                <th>Name</th>
                <th>Active</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse($warehouses as $w)
                <tr>
                    <td>{{ $w->code }}</td>
                    <td>{{ $w->name }}</td>
                    <td>{{ $w->is_active ? 'Yes' : 'No' }}</td>
                    <td>
                        <a href="{{ route('inventory.warehouses.show', $w->id) }}" class="btn btn-secondary">View</a>
                        <a href="{{ route('inventory.warehouses.edit', $w->id) }}" class="btn btn-secondary">Edit</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="4">No warehouses found.</td></tr>
            @endforelse
        </tbody>
    </table>
    {{ $warehouses->withQueryString()->links() }}
</div>
@endsection
