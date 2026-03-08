@extends('noman-inventory::layouts.app')

@section('title', 'Document ' . $document->document_number)

@section('content')
<div class="inventory-card">
    <h1>Document: {{ $document->document_number }}</h1>
    <p class="text-sm">Type: {{ $document->document_type ?? '—' }} · Status: {{ $document->status->label() ?? $document->status }}</p>
    <div class="flex" style="margin-bottom:1rem;">
        <a href="{{ route('inventory.documents.index') }}" class="btn btn-secondary">Back to List</a>
    </div>
    <table class="inventory-table" style="max-width:500px;">
        <tr><th>Document number</th><td>{{ $document->document_number }}</td></tr>
        <tr><th>Type</th><td>{{ $document->document_type ?? '—' }}</td></tr>
        <tr><th>Status</th><td>{{ $document->status->label() ?? $document->status }}</td></tr>
        <tr><th>Document date</th><td>{{ $document->document_date ? \Carbon\Carbon::parse($document->document_date)->format('Y-m-d') : '—' }}</td></tr>
    </table>
    <h2 style="margin-top:1.5rem;">Lines</h2>
    <table class="inventory-table">
        <thead>
            <tr>
                <th>Item</th>
                <th>Quantity</th>
                <th>Warehouse</th>
                <th>Batch</th>
            </tr>
        </thead>
        <tbody>
            @foreach($document->lines ?? [] as $line)
                <tr>
                    <td>{{ $line->item->name ?? $line->item_id }}</td>
                    <td>{{ $line->quantity ?? 0 }}</td>
                    <td>{{ $line->warehouse->code ?? $line->warehouse_id }}</td>
                    <td>{{ $line->batch->batch_code ?? ($line->batch_id ?: '—') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
