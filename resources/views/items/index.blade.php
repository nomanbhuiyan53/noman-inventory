@extends('noman-inventory::layouts.app')

@section('title', 'Items')

@section('content')
<div class="inventory-card">
    <h1>Items</h1>
    <p class="text-sm">Stock masters — items that can be received, issued, and transferred.</p>
    <div class="flex" style="margin-bottom:1rem;">
        <a href="{{ route('inventory.items.create') }}" class="btn btn-primary">Add Item</a>
    </div>

    <form method="get" class="form-group" style="max-width:400px;">
        <label for="search">Search</label>
        <input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="Name, code, SKU">
        <button type="submit" class="btn btn-secondary" style="margin-top:0.5rem;">Search</button>
    </form>

    <table class="inventory-table">
        <thead>
            <tr>
                <th>Code</th>
                <th>Name</th>
                <th>SKU</th>
                <th>Active</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse($items as $item)
                <tr>
                    <td>{{ $item->code }}</td>
                    <td>{{ $item->name }}</td>
                    <td>{{ $item->sku ?? '—' }}</td>
                    <td>{{ $item->is_active ? 'Yes' : 'No' }}</td>
                    <td>
                        <a href="{{ route('inventory.items.show', $item->id) }}" class="btn btn-secondary">View</a>
                        <a href="{{ route('inventory.items.edit', $item->id) }}" class="btn btn-secondary">Edit</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5">No items found.</td></tr>
            @endforelse
        </tbody>
    </table>
    {{ $items->withQueryString()->links() }}
</div>
@endsection
