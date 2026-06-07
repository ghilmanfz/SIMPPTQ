@extends('layouts.app')
@section('title', 'Jadwal Hari Ini')

@section('content')
<div class="space-y-5">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <a href="{{ route('app.jadwal.index') }}" class="text-xs font-bold text-slate-500 hover:text-brand-navy"><i class="ri-arrow-left-line"></i> Jadwal Mingguan (Master)</a>
        <form method="GET" class="flex items-end gap-2">
            <div>
                <label class="text-xs font-bold text-slate-500 uppercase">Tanggal</label>
                <input type="date" name="date" value="{{ $date->toDateString() }}" class="mt-1 rounded-xl border border-slate-200 px-3 py-2 text-sm">
            </div>
            <button class="rounded-xl bg-brand-navy text-white px-4 py-2 text-sm font-bold hover:bg-brand-navy-dark">Lihat</button>
        </form>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
        <div class="px-5 py-4 bg-brand-navy text-white">
            <h3 class="font-bold">{{ hari_indo($date) }}, {{ tgl($date) }}</h3>
            <p class="text-[11px] text-slate-300">Jadwal efektif — sudah memperhitungkan tukar jam yang disetujui pada tanggal ini.</p>
        </div>
        <div class="divide-y divide-slate-100">
            @forelse ($jadwals as $j)
                @php($ex = $j->exceptions->first())
                @php($diganti = $ex && $ex->type === 'Tukar' && $ex->substitute)
                <div class="px-5 py-3 flex items-center justify-between gap-3 {{ $diganti ? 'bg-amber-50/60' : '' }}">
                    <div class="flex items-center gap-3 min-w-0">
                        <span class="h-10 w-10 rounded-xl bg-brand-sky text-brand-navy flex flex-col items-center justify-center text-[10px] font-bold leading-none">
                            <span>{{ jam($j->start_time) }}</span>
                        </span>
                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-brand-navy truncate">{{ $j->mapel?->name ?? 'Halaqah' }} <span class="text-xs font-normal text-slate-400">· {{ $j->kelas?->name }} · {{ $j->sesi?->name }}</span></p>
                            @if ($diganti)
                                <p class="text-xs text-slate-500">
                                    <span class="font-bold text-amber-600">{{ $ex->substitute->name }}</span>
                                    <span class="text-slate-400">menggantikan</span>
                                    <span class="line-through text-slate-400">{{ $j->personil?->name }}</span>
                                </p>
                            @else
                                <p class="text-xs text-slate-500">{{ $j->personil?->name }}</p>
                            @endif
                        </div>
                    </div>
                    @if ($diganti)
                        <span class="shrink-0 text-[10px] font-bold px-2.5 py-1 rounded-full bg-amber-100 text-amber-700"><i class="ri-swap-box-line"></i> Tukar Jam</span>
                    @endif
                </div>
            @empty
                <div class="px-5 py-12 text-center text-slate-400 text-sm">Tidak ada jadwal pada hari {{ $dayName }}.</div>
            @endforelse
        </div>
    </div>
</div>
@endsection
