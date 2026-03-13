@extends('noman-inventory::layouts.app')

@section('title', 'Document ' . $document->document_number)

@section('breadcrumbs')
    <span class="text-slate-400">/</span>
    <a href="{{ route('inventory.documents.index') }}" class="hover:text-slate-900">Documents</a>
    <span class="text-slate-400">/</span>
    <span class="text-slate-900 truncate">{{ $document->document_number }}</span>
@stop

@section('content')
<div class="space-y-6">
    <header class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900">Document: {{ $document->document_number }}</h1>
            <p class="mt-1 text-sm text-slate-600">Type: {{ $document->document_type ?? '—' }} · Status: {{ $document->status->label() }}</p>
        </div>
        <a href="{{ route('inventory.documents.index') }}" class="inline-flex items-center px-4 py-2 rounded-lg border border-slate-300 bg-white text-slate-700 text-sm font-medium hover:bg-slate-50">Back to List</a>
    </header>

    <div class="bg-white rounded-lg shadow p-6 max-w-2xl">
        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div class="py-2 border-b border-slate-100"><dt class="text-xs font-medium text-slate-500 uppercase">Document number</dt><dd class="mt-0.5 text-slate-900">{{ $document->document_number }}</dd></div>
            <div class="py-2 border-b border-slate-100"><dt class="text-xs font-medium text-slate-500 uppercase">Type</dt><dd class="mt-0.5 text-slate-600">{{ $document->document_type ?? '—' }}</dd></div>
            <div class="py-2 border-b border-slate-100"><dt class="text-xs font-medium text-slate-500 uppercase">Status</dt><dd class="mt-0.5"><span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-slate-100 text-slate-800">{{ $document->status->label() }}</span></dd></div>
            <div class="py-2 border-b border-slate-100"><dt class="text-xs font-medium text-slate-500 uppercase">Document date</dt><dd class="mt-0.5 text-slate-600">{{ $document->document_date ? \Carbon\Carbon::parse($document->document_date)->format('Y-m-d') : '—' }}</dd></div>
        </dl>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-lg font-semibold text-slate-900 mb-4">Lines</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead>
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Item</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Quantity</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Warehouse</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Batch</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @foreach($document->lines ?? [] as $line)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3 text-sm text-slate-900">{{ $line->item->name ?? $line->item_id }}</td>
                            <td class="px-4 py-3 text-sm text-slate-600">{{ $line->quantity ?? 0 }}</td>
                            <td class="px-4 py-3 text-sm text-slate-600">{{ $line->warehouse->code ?? $line->warehouse_id }}</td>
                            <td class="px-4 py-3 text-sm text-slate-600">{{ $line->batch->batch_code ?? ($line->batch_id ?: '—') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
