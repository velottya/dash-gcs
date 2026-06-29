@props([
    'position' => 'bottom-right',
    'size' => 'w-32 h-32',
])

@php
    $corner = match ($position) {
        'bottom-right' => 'bottom-0 right-0',
        'bottom-left' => 'bottom-0 left-0 -scale-x-100',
        'top-right' => 'top-0 right-0 -scale-y-100',
        'top-left' => 'top-0 left-0 -scale-x-100 -scale-y-100',
        default => 'bottom-0 right-0',
    };
@endphp

<div {{ $attributes->merge(['class' => "pointer-events-none absolute {$corner} {$size}"]) }}>
    <svg viewBox="0 0 100 100" preserveAspectRatio="none" class="h-full w-full" xmlns="http://www.w3.org/2000/svg">
        <defs>
            <linearGradient id="supergrafis-fill" x1="0%" y1="100%" x2="100%" y2="0%">
                <stop offset="0%" stop-color="#0F261F" />
                <stop offset="45%" stop-color="#2F6C3F" />
                <stop offset="100%" stop-color="#DAA628" />
            </linearGradient>
        </defs>

        <path d="M0,100 C20,80 35,45 50,30 C65,15 80,5 100,0 L100,100 Z" fill="url(#supergrafis-fill)" />
        <path d="M0,100 C20,80 35,45 50,30 C65,15 80,5 100,0" fill="none" stroke="#F6D30F" stroke-width="1.5" />
    </svg>

    @if ((string) $slot !== '')
        <div class="absolute right-3 bottom-3 text-right">
            {{ $slot }}
        </div>
    @endif
</div>
