@extends('noman-inventory::layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="space-y-6">
    <header class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900">Inventory Dashboard</h1>
            <p class="mt-1 text-sm text-slate-600">Manage items, warehouses, stock movements, and reports.</p>
        </div>
    </header>

    {{-- Stat cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-lg shadow p-6">
            <p class="text-sm font-medium text-slate-500">Total items</p>
            <p class="mt-1 text-2xl font-semibold text-slate-900">{{ number_format($itemsCount) }}</p>
            <a href="{{ route('inventory.items.index') }}" class="mt-2 inline-block text-sm text-blue-600 hover:text-blue-800">View all</a>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <p class="text-sm font-medium text-slate-500">Warehouses</p>
            <p class="mt-1 text-2xl font-semibold text-slate-900">{{ number_format($warehousesCount) }}</p>
            <a href="{{ route('inventory.warehouses.index') }}" class="mt-2 inline-block text-sm text-blue-600 hover:text-blue-800">View all</a>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <p class="text-sm font-medium text-slate-500">Posted documents</p>
            <p class="mt-1 text-2xl font-semibold text-slate-900">{{ number_format($documentsCount) }}</p>
            <a href="{{ route('inventory.documents.index') }}" class="mt-2 inline-block text-sm text-blue-600 hover:text-blue-800">View all</a>
        </div>
    </div>

    {{-- Quick actions --}}
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-lg font-semibold text-slate-900 mb-4">Quick actions</h2>
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
            <a href="{{ route('inventory.stock.receive') }}" class="flex flex-col items-center justify-center rounded-lg border border-slate-200 bg-slate-50 p-4 text-center hover:bg-slate-100 hover:border-slate-300 transition-colors">
                <span class="text-slate-700 font-medium">Receive</span>
                <span class="text-xs text-slate-500 mt-0.5">Stock in</span>
            </a>
            <a href="{{ route('inventory.stock.issue') }}" class="flex flex-col items-center justify-center rounded-lg border border-slate-200 bg-slate-50 p-4 text-center hover:bg-slate-100 hover:border-slate-300 transition-colors">
                <span class="text-slate-700 font-medium">Issue</span>
                <span class="text-xs text-slate-500 mt-0.5">Stock out</span>
            </a>
            <a href="{{ route('inventory.stock.transfer') }}" class="flex flex-col items-center justify-center rounded-lg border border-slate-200 bg-slate-50 p-4 text-center hover:bg-slate-100 hover:border-slate-300 transition-colors">
                <span class="text-slate-700 font-medium">Transfer</span>
                <span class="text-xs text-slate-500 mt-0.5">Between warehouses</span>
            </a>
            <a href="{{ route('inventory.stock.adjust') }}" class="flex flex-col items-center justify-center rounded-lg border border-slate-200 bg-slate-50 p-4 text-center hover:bg-slate-100 hover:border-slate-300 transition-colors">
                <span class="text-slate-700 font-medium">Adjust</span>
                <span class="text-xs text-slate-500 mt-0.5">Adjustments</span>
            </a>
            <a href="{{ route('inventory.stock-counts.index') }}" class="flex flex-col items-center justify-center rounded-lg border border-slate-200 bg-slate-50 p-4 text-center hover:bg-slate-100 hover:border-slate-300 transition-colors">
                <span class="text-slate-700 font-medium">Stock count</span>
                <span class="text-xs text-slate-500 mt-0.5">Start count</span>
            </a>
            <a href="{{ route('inventory.reports.index') }}" class="flex flex-col items-center justify-center rounded-lg border border-slate-200 bg-slate-50 p-4 text-center hover:bg-slate-100 hover:border-slate-300 transition-colors">
                <span class="text-slate-700 font-medium">Reports</span>
                <span class="text-xs text-slate-500 mt-0.5">View reports</span>
            </a>
        </div>
    </div>

    {{-- Recent documents --}}
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-slate-900">Recent documents</h2>
            <a href="{{ route('inventory.documents.index') }}" class="text-sm text-blue-600 hover:text-blue-800">View all</a>
        </div>
        @if($recentDocuments->isEmpty())
            <p class="text-slate-500 text-sm py-4">No posted documents yet.</p>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead>
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Number</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Type</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Posted</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase tracking-wider">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @foreach($recentDocuments as $doc)
                            <tr class="hover:bg-slate-50">
                                <td class="px-4 py-3 text-sm text-slate-900">{{ $doc->document_number }}</td>
                                <td class="px-4 py-3 text-sm text-slate-600">{{ $doc->document_type }}</td>
                                <td class="px-4 py-3 text-sm text-slate-600">{{ $doc->posted_at?->format('M j, Y H:i') }}</td>
                                <td class="px-4 py-3 text-right">
                                    <a href="{{ route('inventory.documents.show', $doc->id) }}" class="text-blue-600 hover:text-blue-800 text-sm">View</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection
