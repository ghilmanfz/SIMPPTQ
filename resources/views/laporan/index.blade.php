@extends('layouts.app')
@section('title', 'Laporan Strategis')

@section('content')
<div class="space-y-5">
    {{-- Filter --}}
    <form method="GET" class="bg-white rounded-2xl border border-slate-200 p-4 flex flex-wrap items-end gap-3 print:hidden">
        <div>
            <label class="text-xs font-bold text-slate-500 uppercase">Dari</label>
            <input type="date" name="start" value="{{ $report['range']['start']->toDateString() }}" class="mt-1 rounded-xl border border-slate-200 px-3 py-2.5 text-sm">
        </div>
        <div>
            <label class="text-xs font-bold text-slate-500 uppercase">Sampai</label>
            <input type="date" name="end" value="{{ $report['range']['end']->toDateString() }}" class="mt-1 rounded-xl border border-slate-200 px-3 py-2.5 text-sm">
        </div>
        <button class="rounded-xl bg-brand-navy text-white px-5 py-2.5 text-sm font-bold hover:bg-brand-navy-dark">Terapkan</button>
        <a href="{{ route('app.reports.export', ['start' => $report['range']['start']->toDateString(), 'end' => $report['range']['end']->toDateString()]) }}" class="rounded-xl bg-brand-green text-white px-4 py-2.5 text-sm font-bold hover:bg-brand-green-dark flex items-center gap-1.5"><i class="ri-file-excel-2-line"></i> Download Excel</a>
        <button type="button" onclick="window.print()" class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-bold text-slate-600 hover:bg-slate-50"><i class="ri-printer-line"></i> Cetak</button>
    </form>

    <p class="text-xs text-slate-400">Periode laporan: {{ tgl($report['range']['start']) }} – {{ tgl($report['range']['end']) }}</p>

    {{-- Ringkasan --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        @include('partials.stat', ['icon' => 'ri-team-line', 'color' => 'brand-navy', 'label' => 'Total Personil', 'value' => $report['personnel']['total']])
        @include('partials.stat', ['icon' => 'ri-group-line', 'color' => 'brand-green', 'label' => 'Santri Aktif', 'value' => $report['santri']['aktif']])
        @include('partials.stat', ['icon' => 'ri-fingerprint-line', 'color' => 'brand-teal', 'label' => 'Kehadiran Personil', 'value' => $report['attendance']['hadir']])
        @include('partials.stat', ['icon' => 'ri-qr-scan-2-line', 'color' => 'amber-500', 'label' => 'Presensi Santri', 'value' => $report['santri_presence']])
    </div>

    <div class="grid lg:grid-cols-2 gap-5">
        {{-- Personil --}}
        <div class="bg-white rounded-2xl border border-slate-200 p-5">
            <h3 class="font-bold text-brand-navy mb-3">Komposisi Personil</h3>
            <div class="grid grid-cols-3 gap-3 text-center mb-4">
                <div class="rounded-xl bg-slate-50 p-3"><p class="text-xl font-bold text-brand-navy">{{ $report['personnel']['pengajar'] }}</p><p class="text-[11px] text-slate-400">Pengajar</p></div>
                <div class="rounded-xl bg-slate-50 p-3"><p class="text-xl font-bold text-brand-navy">{{ $report['personnel']['non_pengajar'] }}</p><p class="text-[11px] text-slate-400">Non-Pengajar</p></div>
                <div class="rounded-xl bg-slate-50 p-3"><p class="text-xl font-bold text-brand-navy">{{ $report['personnel']['aktif'] }}</p><p class="text-[11px] text-slate-400">Aktif</p></div>
            </div>
            <p class="text-[11px] font-bold uppercase text-slate-400 mb-2">Per Status Kerja</p>
            @foreach ($report['personnel']['by_status'] as $status => $count)
                <div class="flex justify-between text-sm py-1 border-b border-slate-50 last:border-0"><span class="text-slate-600">{{ $status }}</span><span class="font-semibold text-brand-navy">{{ $count }}</span></div>
            @endforeach
        </div>

        {{-- Santri per kelas --}}
        <div class="bg-white rounded-2xl border border-slate-200 p-5">
            <h3 class="font-bold text-brand-navy mb-3">Santri per Kelas</h3>
            @forelse ($report['santri']['by_kelas'] as $k)
                <div class="flex justify-between text-sm py-1 border-b border-slate-50 last:border-0"><span class="text-slate-600">{{ $k->name }}</span><span class="font-semibold text-brand-navy">{{ $k->santris_count }}</span></div>
            @empty
                <p class="text-sm text-slate-400">Belum ada kelas.</p>
            @endforelse
        </div>

        {{-- Izin/Cuti --}}
        <div class="bg-white rounded-2xl border border-slate-200 p-5">
            <h3 class="font-bold text-brand-navy mb-3">Rekap Izin / Cuti</h3>
            @forelse ($report['leaves'] as $status => $count)
                <div class="flex justify-between text-sm py-1 border-b border-slate-50 last:border-0"><span class="text-slate-600">{{ $status }}</span><span class="font-semibold text-brand-navy">{{ $count }}</span></div>
            @empty
                <p class="text-sm text-slate-400">Tidak ada pengajuan pada periode ini.</p>
            @endforelse
        </div>

        {{-- Perilaku & kunjungan --}}
        <div class="bg-white rounded-2xl border border-slate-200 p-5">
            <h3 class="font-bold text-brand-navy mb-3">Perilaku &amp; Kunjungan</h3>
            <div class="grid grid-cols-3 gap-3 text-center">
                <div class="rounded-xl bg-emerald-50 p-3"><p class="text-xl font-bold text-emerald-600">{{ $report['behavior']['kebaikan'] }}</p><p class="text-[11px] text-slate-400">Kebaikan</p></div>
                <div class="rounded-xl bg-red-50 p-3"><p class="text-xl font-bold text-red-500">{{ $report['behavior']['pelanggaran'] }}</p><p class="text-[11px] text-slate-400">Pelanggaran</p></div>
                <div class="rounded-xl bg-brand-sky p-3"><p class="text-xl font-bold text-brand-navy">{{ $report['visits'] }}</p><p class="text-[11px] text-slate-400">Kunjungan</p></div>
            </div>
        </div>
    </div>
</div>
@endsection
