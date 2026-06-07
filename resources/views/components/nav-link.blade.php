@props(['route' => null, 'icon' => 'ri-circle-line', 'pattern' => null])

@php($isActive = $route ? request()->routeIs($pattern ?? $route) : false)

<a href="{{ $route ? route($route) : '#' }}"
   class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-bold transition-all {{ $isActive ? 'bg-white/15 text-white shadow-sm' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
    <i class="{{ $icon }} text-base shrink-0"></i>
    <span class="truncate">{{ $slot }}</span>
</a>
