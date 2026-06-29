@php($menu = app(\App\Services\MenuService::class)->tree())

<aside class="flex h-screen w-64 flex-shrink-0 flex-col bg-dark text-white">
    <div class="flex items-center gap-3 px-5 py-5">
        <img src="{{ asset('logo.png') }}" alt="GCS" class="h-10 w-10">
        <div>
            <p class="font-heading text-sm font-extrabold leading-tight">DASH GCS</p>
            <p class="text-[11px] text-white/50">Gresik Cipta Sejahtera</p>
        </div>
    </div>

    <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-2">
        <a
            href="{{ route('dashboard1') }}"
            class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-white/90 transition hover:bg-white/10 {{ request()->routeIs('dashboard1') ? 'bg-white/10' : '' }}">
            <i class="fas fa-home w-4 text-center"></i>
            <span>Dashboard</span>
        </a>

        @foreach ($menu as $group)
            <details class="group" {{ request()->is($group->ALAMAT ?? '___none___') ? 'open' : '' }}>
                <summary class="flex cursor-pointer list-none items-center justify-between rounded-lg px-3 py-2 text-sm font-medium text-white/90 transition hover:bg-white/10">
                    <span class="flex items-center gap-3">
                        <i class="{{ $group->ICON }} w-4 text-center"></i>
                        <span>{{ $group->NAMA_MENU }}</span>
                    </span>
                    <i class="fas fa-angle-down text-xs text-white/50 transition group-open:rotate-180"></i>
                </summary>

                <div class="mt-1 space-y-1 pl-4">
                    @foreach ($group->children as $child)
                        @if (count($child->children))
                            <details class="group">
                                <summary class="flex cursor-pointer list-none items-center justify-between rounded-lg px-3 py-2 text-sm text-white/80 transition hover:bg-white/10">
                                    <span class="flex items-center gap-3">
                                        <i class="{{ $child->ICON }} w-4 text-center"></i>
                                        <span>{{ $child->NAMA_MENU }}</span>
                                    </span>
                                    <i class="fas fa-angle-down text-xs text-white/50 transition group-open:rotate-180"></i>
                                </summary>
                                <div class="mt-1 space-y-1 pl-4">
                                    @foreach ($child->children as $leaf)
                                        <a
                                            href="{{ url($leaf->ALAMAT) }}"
                                            class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm text-white/70 transition hover:bg-white/10 hover:text-white">
                                            <i class="{{ $leaf->ICON }} w-4 text-center"></i>
                                            <span>{{ $leaf->NAMA_MENU }}</span>
                                        </a>
                                    @endforeach
                                </div>
                            </details>
                        @else
                            <a
                                href="{{ url($child->ALAMAT) }}"
                                class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm text-white/80 transition hover:bg-white/10 hover:text-white">
                                <i class="{{ $child->ICON }} w-4 text-center"></i>
                                <span>{{ $child->NAMA_MENU }}</span>
                            </a>
                        @endif
                    @endforeach
                </div>
            </details>
        @endforeach
    </nav>

    <div class="border-t border-white/10 px-5 py-4">
        <p class="text-[11px] text-white/40">&copy; {{ now()->year }} GCS</p>
    </div>
</aside>
