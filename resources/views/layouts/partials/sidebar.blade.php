@php
    $current = request()->route()?->getName() ?? '';
    $nav = [
        'Dashboard' => [['name' => 'Dashboard', 'route' => 'inventory.dashboard', 'icon' => 'dashboard']],
        'Catalog' => [
            ['name' => 'Items', 'route' => 'inventory.items.index', 'icon' => 'items'],
            ['name' => 'Warehouses', 'route' => 'inventory.warehouses.index', 'icon' => 'warehouse'],
        ],
        'Stock' => [
            ['name' => 'Receive', 'route' => 'inventory.stock.receive', 'icon' => 'receive'],
            ['name' => 'Issue', 'route' => 'inventory.stock.issue', 'icon' => 'issue'],
            ['name' => 'Transfer', 'route' => 'inventory.stock.transfer', 'icon' => 'transfer'],
            ['name' => 'Adjust', 'route' => 'inventory.stock.adjust', 'icon' => 'adjust'],
        ],
        'Documents' => [['name' => 'Documents', 'route' => 'inventory.documents.index', 'icon' => 'documents']],
        'Stock counts' => [
            ['name' => 'Stock counts', 'route' => 'inventory.stock-counts.index', 'icon' => 'count'],
        ],
        'Reports' => [
            ['name' => 'Reports', 'route' => 'inventory.reports.index', 'icon' => 'reports'],
        ],
    ];
@endphp
<aside id="inventory-sidebar" class="fixed top-0 left-0 z-40 w-64 h-screen pt-16 transition-transform -translate-x-full bg-slate-800 border-r border-slate-700 md:translate-x-0" aria-label="Inventory sidebar">
    <div class="h-full px-3 pb-4 overflow-y-auto">
        <ul class="space-y-1 font-medium">
            @foreach($nav as $group => $links)
                <li>
                    <span class="flex items-center px-3 py-2 text-slate-400 text-xs font-semibold uppercase tracking-wider">{{ $group }}</span>
                </li>
                @foreach($links as $link)
                    @php $active = $current === $link['route'] || str_starts_with($current, $link['route'] . '.'); @endphp
                    <li>
                        <a href="{{ route($link['route']) }}" class="flex items-center px-3 py-2 rounded-lg {{ $active ? 'bg-slate-700 text-white' : 'text-slate-300 hover:bg-slate-700 hover:text-white' }}">
                            <span class="flex-1">{{ $link['name'] }}</span>
                        </a>
                    </li>
                @endforeach
            @endforeach
        </ul>
    </div>
</aside>
<div id="inventory-sidebar-backdrop" class="fixed inset-0 z-30 hidden bg-slate-900/50 md:hidden" aria-hidden="true"></div>
