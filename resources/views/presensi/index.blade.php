@extends('layouts.app')
@section('title', 'Presensi GPS')

@section('content')
@php($demo = $lokasi->first())
<div class="grid lg:grid-cols-3 gap-6">
    {{-- Panel check-in --}}
    <div class="lg:col-span-1 space-y-4" x-data="presensiGeo()">
        @if (! $personil)
            <div class="bg-amber-50 border border-amber-200 rounded-2xl p-5 text-sm text-amber-700">Akun Anda belum terhubung dengan data personil. Hubungi admin.</div>
        @else
            <div class="bg-white rounded-2xl border border-slate-200 p-5">
                <h3 class="font-bold text-brand-navy mb-1">Presensi Hari Ini</h3>
                <p class="text-xs text-slate-400 mb-4">{{ tgl(now(), true) }}</p>

                <div class="rounded-xl bg-slate-50 border border-slate-100 p-4 mb-4 text-center">
                    @if ($todayPresence && $todayPresence->check_in_time)
                        <p class="text-3xl font-extrabold text-brand-navy">{{ jam($todayPresence->check_in_time) }}</p>
                        <p class="text-xs text-slate-500">Check-in · {{ $todayPresence->lokasi?->name }}</p>
                        <span class="inline-block mt-1 text-[10px] font-bold px-2 py-0.5 rounded-full {{ $todayPresence->status === 'Terlambat' ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700' }}">{{ $todayPresence->status }}</span>
                        @if ($todayPresence->check_out_time)
                            <p class="mt-3 text-sm font-bold text-slate-600">Pulang: {{ jam($todayPresence->check_out_time) }}</p>
                        @endif
                    @else
                        <p class="text-sm text-slate-500">Belum check-in hari ini.</p>
                    @endif
                </div>

                {{-- Status GPS --}}
                <div class="rounded-xl border border-slate-100 px-3 py-2 mb-3 text-xs flex items-center gap-2" :class="lat ? 'text-emerald-600' : 'text-slate-400'">
                    <i class="ri-map-pin-line"></i>
                    <span x-text="statusText">Lokasi belum diambil.</span>
                </div>

                <div class="space-y-2">
                    <button type="button" @click="locate()" class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-bold text-brand-navy hover:bg-slate-50 flex items-center justify-center gap-2">
                        <i class="ri-focus-3-line"></i> Ambil Lokasi GPS
                    </button>
                    @if ($demo)
                        <button type="button" @click="useDemo()" class="w-full rounded-xl border border-dashed border-slate-300 px-4 py-2 text-xs font-semibold text-slate-500 hover:bg-slate-50">
                            Gunakan lokasi pondok (simulasi)
                        </button>
                    @endif

                    <div class="grid grid-cols-2 gap-2 pt-1">
                        <form method="POST" action="{{ route('app.presensi.checkin') }}">
                            @csrf
                            <input type="hidden" name="latitude" :value="lat">
                            <input type="hidden" name="longitude" :value="lng">
                            <button :disabled="!lat" class="w-full rounded-xl bg-brand-green text-white px-4 py-2.5 text-sm font-bold hover:bg-brand-green-dark disabled:opacity-40 disabled:cursor-not-allowed">Check In</button>
                        </form>
                        <form method="POST" action="{{ route('app.presensi.checkout') }}">
                            @csrf
                            <input type="hidden" name="latitude" :value="lat">
                            <input type="hidden" name="longitude" :value="lng">
                            <button :disabled="!lat" class="w-full rounded-xl bg-brand-navy text-white px-4 py-2.5 text-sm font-bold hover:bg-brand-navy-dark disabled:opacity-40 disabled:cursor-not-allowed">Check Out</button>
                        </form>
                    </div>
                </div>
            </div>

            @if ($lokasi->isNotEmpty())
                <div class="bg-white rounded-2xl border border-slate-200 p-4">
                    <p class="text-xs font-bold text-slate-500 uppercase mb-2">Lokasi Presensi Aktif</p>
                    @foreach ($lokasi as $l)
                        <div class="flex items-center justify-between text-xs py-1.5 border-b border-slate-50 last:border-0">
                            <span class="text-slate-600">{{ $l->name }}</span>
                            <span class="text-slate-400">radius {{ $l->radius }} m</span>
                        </div>
                    @endforeach
                </div>
            @endif
        @endif
    </div>

    {{-- Riwayat --}}
    <div class="lg:col-span-2">
        <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100"><h3 class="font-bold text-brand-navy">Riwayat Presensi Saya</h3></div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 text-slate-500 text-xs uppercase">
                        <tr><th class="text-left px-5 py-3">Tanggal</th><th class="text-left px-5 py-3">Masuk</th><th class="text-left px-5 py-3">Pulang</th><th class="text-left px-5 py-3">Lokasi</th><th class="text-left px-5 py-3">Status</th></tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($myLogs as $log)
                            <tr>
                                <td class="px-5 py-3 font-semibold text-slate-700">{{ tgl($log->date) }}</td>
                                <td class="px-5 py-3">{{ jam($log->check_in_time) }}</td>
                                <td class="px-5 py-3">{{ $log->check_out_time ? jam($log->check_out_time) : '-' }}</td>
                                <td class="px-5 py-3 text-slate-500">{{ $log->lokasi?->name ?? '-' }}</td>
                                <td class="px-5 py-3"><span class="text-[10px] font-bold px-2 py-0.5 rounded-full {{ $log->status === 'Terlambat' ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700' }}">{{ $log->status }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-5 py-8 text-center text-slate-400">Belum ada riwayat presensi.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function presensiGeo() {
        return {
            lat: '', lng: '', statusText: 'Lokasi belum diambil.',
            locate() {
                if (!navigator.geolocation) { this.statusText = 'Browser tidak mendukung GPS.'; return; }
                this.statusText = 'Mengambil lokasi...';
                navigator.geolocation.getCurrentPosition(
                    (pos) => { this.lat = pos.coords.latitude.toFixed(7); this.lng = pos.coords.longitude.toFixed(7); this.statusText = 'Lokasi terdeteksi: ' + this.lat + ', ' + this.lng; },
                    () => { this.statusText = 'Gagal mengambil lokasi. Izinkan akses GPS atau pakai simulasi.'; }
                );
            },
            useDemo() {
                @if ($demo)
                    this.lat = '{{ $demo->latitude }}'; this.lng = '{{ $demo->longitude }}';
                    this.statusText = 'Memakai koordinat pondok (simulasi): ' + this.lat + ', ' + this.lng;
                @endif
            },
        };
    }
</script>
@endpush
