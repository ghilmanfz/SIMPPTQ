@php
    $map = [
        'brand-navy' => ['bg' => 'bg-brand-navy/10', 'text' => 'text-brand-navy'],
        'brand-green' => ['bg' => 'bg-brand-green/10', 'text' => 'text-brand-green'],
        'brand-teal' => ['bg' => 'bg-brand-teal/10', 'text' => 'text-brand-teal'],
        'amber-500' => ['bg' => 'bg-amber-100', 'text' => 'text-amber-600'],
        'red-500' => ['bg' => 'bg-red-100', 'text' => 'text-red-600'],
    ];
    $c = $map[$color] ?? $map['brand-navy'];
@endphp
<div class="bg-white rounded-2xl border border-slate-200 p-4 flex items-center gap-4">
    <div class="h-12 w-12 rounded-xl flex items-center justify-center {{ $c['bg'] }} {{ $c['text'] }}">
        <i class="{{ $icon }} text-xl"></i>
    </div>
    <div class="min-w-0">
        <p class="text-2xl font-extrabold text-brand-navy leading-none">{{ $value }}</p>
        <p class="text-[11px] font-semibold text-slate-400 mt-1 truncate">{{ $label }}</p>
    </div>
</div>
