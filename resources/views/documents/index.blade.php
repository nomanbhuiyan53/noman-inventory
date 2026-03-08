@extends('noman-inventory::layouts.app')

@section('title', 'Stock Documents')

@section('content')
<div class="inventory-card">
    <h1>Stock Documents</h1>
    <table class="inventory-table">
        <thead>
            <tr>
                <th>Number</th>
                <th>Type</th>
                <th>Status</th>
                <th>Date</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse($documents as $doc)
                <tr>
                    <td>{{ $doc->document_number }}</td>
                    <td>{{ $doc->document_type ?? '—' }}</td>
                    <td>{{ $doc->status->label() ?? $doc->status }}</td>
                    <td>{{ $doc->document_date ? \Carbon\Carbon::parse($doc->document_date)->format('Y-m-d') : '—' }}</td>
                    <td>
                        <a href="{{ route('inventory.documents.show', $doc->id) }}" class="btn btn-secondary">View</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5">No documents found.</td></tr>
            @endforelse
        </tbody>
    </table>
    {{ $documents->withQueryString()->links() }}
</div>
@endsection
