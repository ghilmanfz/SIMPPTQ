@extends('layouts.app')
@section('title', 'Riwayat Kelas — '.$santri->name)

@php
    $meta = fn ($action) => match ($action) {
        'Penempatan' => ['ri-user-add-line', 'bg-blue-100 text-blue-600'],
        'Pindah Kelas' => ['ri-arrow-left-right-line', 'bg-indigo-100 text-indigo-600'],
        'Naik Kelas' => ['ri-arrow-up-double-line', 'bg-emerald-100 text-emerald-600'],
        'Tinggal Kelas' => ['ri-repeat-line', 'bg-amber-100 text-amber-600'],
        'Lulus' => ['ri-graduation-cap-line', 'bg-green-100 text-green-700'],
        'Keluar' => ['ri-logout-box-line', 'bg-red-100 text-red-500'],
        default => ['ri-history-line', 'bg-slate-100 text-slate-500'],
    };
@endphp

@section('content')
<div class="max-w-2xl space-y-5">
    <a href="{{ route('app.santri.index') }}" class="text-xs font-bold text-slate-500 hover:text-brand-navy"><i class="ri-arrow-left-line"></i> Kembali ke Data Santri</a>

    {{-- Header santri --}}
    <div class="bg-white rounded-2xl border border-slate-200 p-5 flex items-center gap-4">
        <div class="h-14 w-14 rounded-full bg-brand-sky text-brand-navy flex items-center justify-center text-lg font-bold overflow-hidden">
            @if ($santri->photoUrl())<img src="{{ $santri->photoUrl() }}" class="h-full w-full object-cover">@else {{ strtoupper(mb_substr($santri->name,0,1)) }} @endif
        </div>
        <div>
            <h2 class="text-lg font-bold text-brand-navy">{{ $santri->name }}</h2>
            <p class="text-xs text-slate-400">NIS {{ $santri->nis }} · Kelas saat ini: <span class="font-semibold text-slate-600">{{ $santri->kelas?->name ?? '-' }}</span> · Status: {{ $santri->status }}</p>
        </div>
    </div>

    {{-- Timeline --}}
    <div class="bg-white rounded-2xl border border-slate-200 p-5">
        <h3 class="font-bold text-brand-navy mb-4">Riwayat Kelas</h3>
        @if ($histories->isEmpty())
            <div class="py-10 text-center text-slate-400 text-sm">Belum ada riwayat perpindahan kelas.</div>
        @else
            <ol class="relative border-l-2 border-slate-100 ml-3 space-y-6">
                @foreach ($histories as $h)
                    @php([$icon, $color] = $meta($h->action))
                    <li class="ml-6">
                        <span class="absolute -left-[15px] flex h-7 w-7 items-center justify-center rounded-full ring-4 ring-white {{ $color }}"><i class="{{ $icon }}"></i></span>
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="text-sm font-bold text-brand-navy">{{ $h->action }}</span>
                            <span class="text-[11px] text-slate-400">{{ tgl($h->created_at, true) }} · {{ jam($h->created_at) }}</span>
                        </div>
                        <p class="text-sm text-slate-600 mt-0.5">
                            @if ($h->from_kelas && $h->to_kelas)
                                {{ $h->from_kelas }} <i class="ri-arrow-right-line text-slate-300"></i> <span class="font-semibold">{{ $h->to_kelas }}</span>
                            @elseif ($h->to_kelas)
                                <span class="font-semibold">{{ $h->to_kelas }}</span>
                            @elseif ($h->from_kelas)
                                Dari {{ $h->from_kelas }} <i class="ri-arrow-right-line text-slate-300"></i> <span class="text-slate-400">tanpa kelas</span>
                            @else
                                <span class="text-slate-400">—</span>
                            @endif
                            @if ($h->tahunAjaran) <span class="text-[11px] text-slate-400">({{ $h->tahunAjaran->name }})</span>@endif
                        </p>
                        @if ($h->note)<p class="text-[11px] text-slate-400 mt-0.5">{{ $h->note }}</p>@endif
                        <p class="text-[10px] text-slate-300 mt-0.5">oleh {{ $h->creator?->name ?? 'Sistem' }}</p>
                    </li>
                @endforeach
            </ol>
        @endif
    </div>
</div>
@endsection
