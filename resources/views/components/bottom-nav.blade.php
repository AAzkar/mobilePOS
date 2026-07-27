@php
    $isAdmin = auth()->check() && auth()->user()->role === 'admin';

    $items = collect([
        ['route' => 'scan', 'label' => 'Scan', 'icon' => 'scan', 'adminOnly' => false],
        ['route' => 'cart', 'label' => 'Cart', 'icon' => 'cart', 'adminOnly' => false],
        ['route' => 'products.index', 'label' => 'Products', 'icon' => 'products', 'adminOnly' => true],
        ['route' => 'reports', 'label' => 'Reports', 'icon' => 'reports', 'adminOnly' => true],
        ['route' => 'settings', 'label' => 'Settings', 'icon' => 'settings', 'adminOnly' => true],
    ])->filter(fn ($item) => ! $item['adminOnly'] || $isAdmin);
@endphp

<nav class="fixed inset-x-0 bottom-0 z-20 border-t border-slate-200 bg-white pb-[env(safe-area-inset-bottom)]">
    <div class="mx-auto flex max-w-lg items-stretch justify-between">
        @foreach ($items as $item)
            @php $active = request()->routeIs($item['route']) || request()->routeIs($item['route'].'.*'); @endphp
            <a
                href="{{ route($item['route']) }}"
                class="flex flex-1 flex-col items-center gap-1 py-2 text-xs {{ $active ? 'text-teal-700' : 'text-slate-500' }}"
            >
                <span class="{{ $active ? 'text-teal-700' : 'text-slate-400' }}">
                    @switch($item['icon'])
                        @case('scan')
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-6 w-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 7V5a2 2 0 0 1 2-2h2M17 3h2a2 2 0 0 1 2 2v2M21 17v2a2 2 0 0 1-2 2h-2M7 21H5a2 2 0 0 1-2-2v-2M3 12h18" />
                            </svg>
                            @break
                        @case('cart')
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-6 w-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 4h2l2.4 12.2a2 2 0 0 0 2 1.6h7.4a2 2 0 0 0 2-1.6L21 8H6" />
                                <circle cx="9" cy="20" r="1" /><circle cx="17" cy="20" r="1" />
                            </svg>
                            @break
                        @case('products')
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-6 w-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M20 7 12 3 4 7m16 0-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                            </svg>
                            @break
                        @case('reports')
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-6 w-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 3v18h18M7 15l4-5 3 3 5-6" />
                            </svg>
                            @break
                        @case('settings')
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-6 w-6">
                                <circle cx="12" cy="12" r="3" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.4 15a1.7 1.7 0 0 0 .34 1.87l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.7 1.7 0 0 0-1.87-.34 1.7 1.7 0 0 0-1.04 1.56V21a2 2 0 1 1-4 0v-.09A1.7 1.7 0 0 0 8.96 19.4a1.7 1.7 0 0 0-1.87.34l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06A1.7 1.7 0 0 0 4.6 15a1.7 1.7 0 0 0-1.56-1.04H3a2 2 0 1 1 0-4h.09A1.7 1.7 0 0 0 4.6 8.96a1.7 1.7 0 0 0-.34-1.87l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06A1.7 1.7 0 0 0 9 4.6a1.7 1.7 0 0 0 1.04-1.56V3a2 2 0 1 1 4 0v.09A1.7 1.7 0 0 0 15 4.6a1.7 1.7 0 0 0 1.87-.34l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06A1.7 1.7 0 0 0 19.4 9a1.7 1.7 0 0 0 1.56 1.04H21a2 2 0 1 1 0 4h-.09A1.7 1.7 0 0 0 19.4 15Z" />
                            </svg>
                            @break
                    @endswitch
                </span>
                {{ $item['label'] }}
            </a>
        @endforeach
    </div>
</nav>
