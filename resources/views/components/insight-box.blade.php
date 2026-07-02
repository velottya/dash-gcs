@props([
    'items' => [],
])

@if (count($items))
    <div {{ $attributes->merge(['class' => 'my-5 rounded-lg border border-green/20 bg-green/5 px-4 py-3 text-xs leading-relaxed text-dark/70']) }}>
        <p class="mb-1.5 font-heading text-[11px] font-extrabold uppercase tracking-wide text-green">Insight</p>
        <ul class="list-inside list-disc space-y-1.5">
            @foreach ($items as $item)
                <li class="pl-0.5">{{ $item }}</li>
            @endforeach
        </ul>
    </div>
@endif
