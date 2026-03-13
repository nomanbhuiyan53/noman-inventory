@extends('noman-inventory::layouts.app')

@section('title', 'Items')

@section('breadcrumbs')
    <span class="text-slate-400">/</span>
    <span class="text-slate-900">Items</span>
@stop

@section('content')
<div class="space-y-6">
    <header class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900">Items</h1>
            <p class="mt-1 text-sm text-slate-600">Stock masters — items that can be received, issued, and transferred.</p>
        </div>
        <a href="{{ route('inventory.items.create') }}" class="inline-flex items-center px-4 py-2 rounded-lg bg-blue-600 text-white text-sm font-medium hover:bg-blue-700">Add Item</a>
    </header>

    <div class="bg-white rounded-lg shadow p-6">
        <form method="get" class="mb-4 flex flex-wrap items-end gap-3">
            <div>
                <label for="search" class="block text-sm font-medium text-slate-700 mb-1">Search</label>
                <input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="Name, code, SKU" class="block w-full max-w-xs rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
            </div>
            <button type="submit" class="inline-flex items-center px-4 py-2 rounded-lg border border-slate-300 bg-white text-slate-700 text-sm font-medium hover:bg-slate-50">Search</button>
        </form>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead>
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Code</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Name</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">SKU</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Active</th>
                        <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse($items as $item)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3 text-sm text-slate-900">{{ $item->code }}</td>
                            <td class="px-4 py-3 text-sm text-slate-900">{{ $item->name }}</td>
                            <td class="px-4 py-3 text-sm text-slate-600">{{ $item->sku ?? '—' }}</td>
                            <td class="px-4 py-3 text-sm text-slate-600">{{ $item->is_active ? 'Yes' : 'No' }}</td>
                            <td class="px-4 py-3 text-right space-x-2">
                                <a href="{{ route('inventory.items.show', $item->id) }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">View</a>
                                <a href="{{ route('inventory.items.edit', $item->id) }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">Edit</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-12 text-center text-slate-500">
                                <p class="mb-2">No items found.</p>
                                <a href="{{ route('inventory.items.create') }}" class="inline-flex items-center px-4 py-2 rounded-lg bg-blue-600 text-white text-sm font-medium hover:bg-blue-700">Add first item</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($items->hasPages())
            <div class="mt-4">{{ $items->withQueryString()->links() }}</div>
        @endif
    </div>
</div>
@endsection
