@extends('noman-inventory::layouts.app')

@section('title', 'Stock Documents')

@section('breadcrumbs')
    <span class="text-slate-400">/</span>
    <span class="text-slate-900">Documents</span>
@stop

@section('content')
<div class="space-y-6">
    <header>
        <h1 class="text-2xl font-semibold text-slate-900">Stock Documents</h1>
        <p class="mt-1 text-sm text-slate-600">Receive, issue, transfer, and adjustment documents.</p>
    </header>

    <div class="bg-white rounded-lg shadow p-6">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead>
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Number</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Type</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Date</th>
                        <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase tracking-wider">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse($documents as $doc)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3 text-sm text-slate-900">{{ $doc->document_number }}</td>
                            <td class="px-4 py-3 text-sm text-slate-600">{{ $doc->document_type ?? '—' }}</td>
                            <td class="px-4 py-3">
                                @php $badge = $doc->status->badgeColor(); @endphp
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                    @if($badge === 'green') bg-emerald-100 text-emerald-800
                                    @elseif($badge === 'yellow') bg-amber-100 text-amber-800
                                    @elseif($badge === 'blue') bg-blue-100 text-blue-800
                                    @elseif($badge === 'orange') bg-orange-100 text-orange-800
                                    @elseif($badge === 'red') bg-red-100 text-red-800
                                    @else bg-slate-100 text-slate-800
                                    @endif">{{ $doc->status->label() }}</span>
                            </td>
                            <td class="px-4 py-3 text-sm text-slate-600">{{ $doc->document_date ? \Carbon\Carbon::parse($doc->document_date)->format('Y-m-d') : '—' }}</td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('inventory.documents.show', $doc->id) }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-12 text-center text-slate-500">No documents found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($documents->hasPages())
            <div class="mt-4">{{ $documents->withQueryString()->links() }}</div>
        @endif
    </div>
</div>
@endsection
