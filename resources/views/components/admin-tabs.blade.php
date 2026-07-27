@php
    $tabs = [
        ['route' => 'products.index', 'label' => 'Products'],
        ['route' => 'inventory.index', 'label' => 'Inventory'],
        ['route' => 'reports', 'label' => 'Reports'],
        ['route' => 'settings', 'label' => 'Settings'],
    ];
@endphp

<div class="mb-4 flex gap-1 overflow-x-auto rounded-lg bg-slate-100 p-1 text-sm">
    @foreach ($tabs as $tab)
        @php $active = request()->routeIs($tab['route']) || request()->routeIs($tab['route'].'.*'); @endphp
        <a
            href="{{ route($tab['route']) }}"
            class="flex-1 whitespace-nowrap rounded-md px-3 py-1.5 text-center {{ $active ? 'bg-white font-medium text-teal-700 shadow-sm' : 'text-slate-500' }}"
        >
            {{ $tab['label'] }}
        </a>
    @endforeach
</div>
