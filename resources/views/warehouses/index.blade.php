@extends('noman-inventory::layouts.app')

@section('title', 'Warehouses')

@section('breadcrumbs')
    <span class="text-slate-400">/</span>
    <span class="text-slate-900">Warehouses</span>
@stop

@section('content')
<div class="space-y-6">
    <header class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900">Warehouses</h1>
            <p class="mt-1 text-sm text-slate-600">Storage locations for inventory.</p>
        </div>
        <a href="{{ route('inventory.warehouses.create') }}" class="inline-flex items-center px-4 py-2 rounded-lg bg-blue-600 text-white text-sm font-medium hover:bg-blue-700">Add Warehouse</a>
    </header>

    <div class="bg-white rounded-lg shadow p-6">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead>
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Code</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Name</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Active</th>
                        <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse($warehouses as $w)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3 text-sm text-slate-900">{{ $w->code }}</td>
                            <td class="px-4 py-3 text-sm text-slate-900">{{ $w->name }}</td>
                            <td class="px-4 py-3 text-sm text-slate-600">{{ $w->is_active ? 'Yes' : 'No' }}</td>
                            <td class="px-4 py-3 text-right space-x-2">
                                <a href="{{ route('inventory.warehouses.show', $w->id) }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">View</a>
                                <a href="{{ route('inventory.warehouses.edit', $w->id) }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">Edit</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-12 text-center text-slate-500">
                                <p class="mb-2">No warehouses found.</p>
                                <a href="{{ route('inventory.warehouses.create') }}" class="inline-flex items-center px-4 py-2 rounded-lg bg-blue-600 text-white text-sm font-medium hover:bg-blue-700">Add first warehouse</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($warehouses->hasPages())
            <div class="mt-4">{{ $warehouses->withQueryString()->links() }}</div>
        @endif
    </div>
</div>
@endsection
