@extends('layouts.app')
@section('title', 'Dashboard')

@php($me = auth()->user())

@section('content')
<div class="space-y-6">

    {{-- Sambutan --}}
    <div class="bg-gradient-to-r from-brand-navy via-[#0c266f] to-brand-navy-dark rounded-2xl p-6 text-white relative overflow-hidden">
        <div class="absolute -right-10 -top-10 h-48 w-48 rounded-full bg-brand-green/20 blur-2xl"></div>
        <div class="relative">
            <p class="text-xs text-slate-300">{{ tgl(now(), true) }}</p>
            <h2 class="text-xl lg:text-2xl font-bold mt-1">Assalamu'alaikum, {{ $me->name }} 👋</h2>
            <p class="text-sm text-slate-300 mt-1">Anda masuk sebagai <span class="font-semibold text-brand-teal">{{ $me->role?->label }}</span>. Semoga harimu berkah.</p>
        </div>
    </div>

    {{-- Ringkasan organisasi (admin / pimpinan) --}}
    @canany(['personnel_view','santri_view','reports_view','user_manage','presence_manage'])
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            @include('partials.stat', ['icon' => 'ri-team-line', 'color' => 'brand-navy', 'label' => 'Personil Aktif', 'value' => $stats['personil']])
            @include('partials.stat', ['icon' => 'ri-group-line', 'color' => 'brand-green', 'label' => 'Santri Aktif', 'value' => $stats['santri']])
            @include('partials.stat', ['icon' => 'ri-fingerprint-line', 'color' => 'brand-teal', 'label' => 'Hadir Hari Ini', 'value' => $stats['present_today']])
            @include('partials.stat', ['icon' => 'ri-qr-scan-2-line', 'color' => 'amber-500', 'label' => 'Presensi Santri', 'value' => $stats['santri_present_today']])
        </div>
    @endcanany

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Kolom utama --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Aktivitas pribadi --}}
            @if ($personal)
                <div class="bg-white rounded-2xl border border-slate-200 p-5">
                    <h3 class="font-bold text-brand-navy mb-4 flex items-center gap-2"><i class="ri-user-star-line text-brand-green"></i> Ringkasan Pribadi</h3>
                    <div class="grid sm:grid-cols-2 gap-4">
                        {{-- Presensi hari ini --}}
                        <div class="rounded-xl bg-slate-50 border border-slate-100 p-4">
                            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Presensi Hari Ini</p>
                            @if ($personal['presence_today'])
                                <p class="mt-2 text-sm font-bold text-brand-navy">Masuk {{ jam($personal['presence_today']->check_in_time) }}
                                    @if ($personal['presence_today']->check_out_time) · Pulang {{ jam($personal['presence_today']->check_out_time) }} @endif
                                </p>
                                <span class="inline-block mt-1 text-[10px] font-bold px-2 py-0.5 rounded-full {{ $personal['presence_today']->status === 'Terlambat' ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700' }}">{{ $personal['presence_today']->status }}</span>
                            @else
                                <p class="mt-2 text-sm text-slate-500">Belum melakukan check-in.</p>
                                @can('presence_gps')
                                    <a href="{{ route('app.presensi.index') }}" class="inline-block mt-2 text-xs font-bold text-brand-green hover:underline">Check-in sekarang →</a>
                                @endcan
                            @endif
                        </div>
                        {{-- Slip gaji terbaru --}}
                        <div class="rounded-xl bg-slate-50 border border-slate-100 p-4">
                            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Slip Gaji Terakhir</p>
                            @if ($personal['latest_payslip'])
                                <p class="mt-2 text-sm font-bold text-brand-navy">{{ rupiah($personal['latest_payslip']->total) }}</p>
                                <p class="text-[11px] text-slate-500">{{ $personal['latest_payslip']->period->name }}</p>
                            @else
                                <p class="mt-2 text-sm text-slate-500">Belum ada slip final.</p>
                            @endif
                        </div>
                    </div>

                    {{-- Jadwal hari ini (pengajar) --}}
                    @if ($personal['schedules_today']->isNotEmpty())
                        <div class="mt-4">
                            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide mb-2">Jadwal Mengajar Hari Ini</p>
                            <div class="space-y-2">
                                @foreach ($personal['schedules_today'] as $jadwal)
                                    <div class="flex items-center justify-between rounded-xl border border-slate-100 px-4 py-2.5">
                                        <div>
                                            <p class="text-sm font-semibold text-brand-navy">{{ $jadwal->mapel?->name ?? 'Halaqah' }} · {{ $jadwal->kelas?->name }}</p>
                                            <p class="text-[11px] text-slate-400">{{ $jadwal->sesi?->name }}</p>
                                        </div>
                                        <span class="text-xs font-bold text-brand-green">{{ jam($jadwal->start_time) }}–{{ jam($jadwal->end_time) }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <div class="mt-4 flex gap-2 text-xs">
                        @if ($personal['pending_leaves'])
                            <span class="px-3 py-1.5 rounded-lg bg-amber-50 text-amber-700 font-semibold">{{ $personal['pending_leaves'] }} izin menunggu</span>
                        @endif
                        @if ($personal['pending_swaps'])
                            <span class="px-3 py-1.5 rounded-lg bg-teal-50 text-teal-700 font-semibold">{{ $personal['pending_swaps'] }} tukar jam menunggu</span>
                        @endif
                    </div>
                </div>
            @endif

            {{-- Grafik tren kehadiran (pimpinan / monitoring) --}}
            @can('reports_view')
                <div class="bg-white rounded-2xl border border-slate-200 p-5">
                    <h3 class="font-bold text-brand-navy mb-4 flex items-center gap-2"><i class="ri-line-chart-line text-brand-green"></i> Tren Kehadiran Personil (7 Hari)</h3>
                    <canvas id="trendChart" height="90"></canvas>
                </div>
            @endcan

            {{-- Antrean persetujuan (admin) --}}
            @canany(['leave_approve','swap_approve'])
                <div class="grid sm:grid-cols-2 gap-4">
                    @can('leave_approve')
                        <a href="{{ route('app.leaves.index') }}" class="block rounded-2xl border border-slate-200 bg-white p-5 hover:border-brand-green transition">
                            <div class="flex items-center justify-between">
                                <i class="ri-file-list-3-line text-2xl text-brand-navy"></i>
                                <span class="text-2xl font-extrabold text-brand-navy">{{ $stats['pending_leaves'] }}</span>
                            </div>
                            <p class="mt-2 text-sm font-semibold text-slate-600">Izin/Cuti menunggu persetujuan</p>
                        </a>
                    @endcan
                    @can('swap_approve')
                        <a href="{{ route('app.swaps.index') }}" class="block rounded-2xl border border-slate-200 bg-white p-5 hover:border-brand-green transition">
                            <div class="flex items-center justify-between">
                                <i class="ri-swap-box-line text-2xl text-brand-navy"></i>
                                <span class="text-2xl font-extrabold text-brand-navy">{{ $stats['pending_swaps'] }}</span>
                            </div>
                            <p class="mt-2 text-sm font-semibold text-slate-600">Tukar jam menunggu persetujuan</p>
                        </a>
                    @endcan
                </div>
            @endcanany
        </div>

        {{-- Kolom samping: pengumuman --}}
        <div class="space-y-6">
            <div class="bg-white rounded-2xl border border-slate-200 p-5">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-bold text-brand-navy flex items-center gap-2"><i class="ri-megaphone-line text-brand-green"></i> Pengumuman</h3>
                    @can('announcement_view')<a href="{{ route('app.announcements.index') }}" class="text-[11px] font-bold text-brand-green hover:underline">Semua</a>@endcan
                </div>
                <div class="space-y-3">
                    @forelse ($announcements as $a)
                        <div class="border-l-2 border-brand-green pl-3">
                            <p class="text-sm font-semibold text-brand-navy">{{ $a->title }}</p>
                            <p class="text-[11px] text-slate-500 line-clamp-2">{{ $a->content }}</p>
                            <p class="text-[10px] text-slate-400 mt-1">{{ tgl($a->published_at) }}</p>
                        </div>
                    @empty
                        <p class="text-sm text-slate-400">Belum ada pengumuman.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
@can('reports_view')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const ctx = document.getElementById('trendChart');
        if (!ctx || typeof Chart === 'undefined') return;
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: @json($trend['labels']),
                datasets: [{
                    label: 'Personil Hadir',
                    data: @json($trend['data']),
                    borderColor: '#107c41',
                    backgroundColor: 'rgba(16,124,65,0.1)',
                    fill: true,
                    tension: 0.35,
                }],
            },
            options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } },
        });
    });
</script>
@endcan
@endpush
