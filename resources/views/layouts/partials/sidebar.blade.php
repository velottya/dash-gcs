@php
    $menu  = app(\App\Services\MenuService::class)->tree();
    $level = (int) session('level');
    use App\Support\Role;
@endphp

<aside id="sidebar" class="relative flex h-screen w-64 shrink-0 flex-col bg-dark text-white">
    <button
        type="button"
        id="sidebar-toggle"
        title="Ciutkan/lebarkan menu"
        class="absolute -right-3 top-6 z-10 flex h-6 w-6 items-center justify-center rounded-full bg-green text-white shadow-md transition hover:bg-green-light">
        <i class="fas fa-angle-left text-xs"></i>
    </button>

    <div class="flex items-center gap-3 px-5 py-5">
        <img src="{{ asset('logo.png') }}" alt="GCS" class="h-10 w-10 shrink-0">
        <div class="sidebar-label">
            <p class="font-heading text-sm font-extrabold leading-tight">DASH GCS</p>
            <p class="text-[11px] text-white/50">Gresik Cipta Sejahtera</p>
        </div>
    </div>

    <nav class="flex-1 space-y-1 overflow-y-auto overflow-x-hidden px-3 py-2">
        <a
            href="{{ route('dashboard') }}"
            title="Dashboard"
            class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-white/90 transition hover:bg-white/10 {{ request()->routeIs('dashboard') ? 'bg-white/10' : '' }}">
            <i class="fas fa-home w-4 text-center"></i>
            <span class="sidebar-label">Dashboard</span>
        </a>

        {{-- RKAP (semua role) --}}
        <a
            href="{{ route('rkap.index') }}"
            title="RKAP"
            class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-white/90 transition hover:bg-white/10 {{ request()->routeIs('rkap.*') ? 'bg-white/10' : '' }}">
            <i class="fas fa-file-invoice-dollar w-4 text-center"></i>
            <span class="sidebar-label">RKAP</span>
        </a>

        {{-- Manajemen User (Superadmin + GM) --}}
        @if ($level === Role::SUPERADMIN || $level === Role::GM)
            <a
                href="{{ route('users.index') }}"
                title="Manajemen User"
                class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-white/90 transition hover:bg-white/10 {{ request()->routeIs('users.*') ? 'bg-white/10' : '' }}">
                <i class="fas fa-users-cog w-4 text-center"></i>
                <span class="sidebar-label">Manajemen User</span>
            </a>
        @endif

        @foreach ($menu as $group)
            <details class="group" {{ request()->is($group->ALAMAT ?? '___none___') ? 'open' : '' }}>
                <summary class="flex cursor-pointer list-none items-center justify-between rounded-lg px-3 py-2 text-sm font-medium text-white/90 transition hover:bg-white/10" title="{{ $group->NAMA_MENU }}">
                    <span class="flex items-center gap-3">
                        <i class="{{ $group->ICON }} w-4 text-center"></i>
                        <span class="sidebar-label">{{ $group->NAMA_MENU }}</span>
                    </span>
                    <i class="fas fa-angle-down sidebar-label text-xs text-white/50 transition group-open:rotate-180"></i>
                </summary>

                <div class="sidebar-submenu mt-1 space-y-1 pl-4">
                    @foreach ($group->children as $child)
                        @if (count($child->children))
                            @foreach ($child->children as $leaf)
                                <a
                                    href="{{ url(strtolower($leaf->ALAMAT)) }}"
                                    class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm text-white/80 transition hover:bg-white/10 hover:text-white">
                                    <i class="fas fa-genderless w-4 text-center"></i>
                                    <span>{{ $leaf->NAMA_MENU }}</span>
                                </a>
                            @endforeach
                        @else
                            <a
                                href="{{ url(strtolower($child->ALAMAT)) }}"
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
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button
                type="submit"
                title="Keluar"
                class="flex w-full items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-white/80 transition hover:bg-white/10 hover:text-white">
                <i class="fas fa-sign-out-alt w-4 text-center"></i>
                <span class="sidebar-label">Keluar</span>
            </button>
        </form>
        <p class="sidebar-label mt-3 text-[11px] text-white/40">&copy; {{ now()->year }} GCS</p>
    </div>
</aside>

<script>
    (function () {
        const sidebar = document.getElementById('sidebar');
        const toggle = document.getElementById('sidebar-toggle');
        const icon = toggle.querySelector('i');

        function applyState(collapsed) {
            sidebar.classList.toggle('collapsed', collapsed);
            icon.classList.toggle('fa-angle-left', !collapsed);
            icon.classList.toggle('fa-angle-right', collapsed);
        }

        applyState(localStorage.getItem('sidebar-collapsed') === '1');

        toggle.addEventListener('click', () => {
            const collapsed = !sidebar.classList.contains('collapsed');
            applyState(collapsed);
            localStorage.setItem('sidebar-collapsed', collapsed ? '1' : '0');
        });
    })();
</script>
