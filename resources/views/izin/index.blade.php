@extends('layouts.app')
@section('title', 'Izin / Cuti')

@php
    $badge = fn ($s) => match ($s) {
        'Disetujui' => 'bg-emerald-100 text-emerald-700',
        'Ditolak' => 'bg-red-100 text-red-600',
        default => 'bg-amber-100 text-amber-700',
    };
@endphp

@section('content')
<div class="grid lg:grid-cols-3 gap-6">
    {{-- Form pengajuan --}}
    @can('leave_apply')
        <div class="lg:col-span-1">
            <div class="bg-white rounded-2xl border border-slate-200 p-5">
                <h3 class="font-bold text-brand-navy mb-4">Ajukan Izin / Cuti</h3>
                <form method="POST" action="{{ route('app.leaves.store') }}" enctype="multipart/form-data" class="space-y-3">
                    @csrf
                    <div>
                        <label class="text-xs font-bold text-slate-500 uppercase">Jenis</label>
                        <select name="type" required class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm">
                            <option>Sakit</option><option>Izin</option><option>Cuti Tahunan</option><option>Dinas Luar</option>
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="text-xs font-bold text-slate-500 uppercase">Dari</label>
                            <input type="date" name="start_date" required class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm">
                        </div>
                        <div>
                            <label class="text-xs font-bold text-slate-500 uppercase">Sampai</label>
                            <input type="date" name="end_date" required class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm">
                        </div>
                    </div>
                    <div>
                        <label class="text-xs font-bold text-slate-500 uppercase">Alasan</label>
                        <textarea name="reason" rows="3" required class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm"></textarea>
                    </div>
                    <div>
                        <label class="text-xs font-bold text-slate-500 uppercase">Lampiran (opsional)</label>
                        <input type="file" name="document" class="mt-1 w-full text-xs text-slate-500 file:mr-3 file:rounded-lg file:border-0 file:bg-slate-100 file:px-3 file:py-2 file:text-xs file:font-semibold">
                    </div>
                    <button class="w-full rounded-xl bg-brand-navy text-white px-4 py-2.5 text-sm font-bold hover:bg-brand-navy-dark">Kirim Pengajuan</button>
                </form>
            </div>
        </div>
    @endcan

    <div class="{{ auth()->user()->can('leave_apply') ? 'lg:col-span-2' : 'lg:col-span-3' }} space-y-6">
        {{-- Antrean approval --}}
        @if ($canApprove)
            <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100"><h3 class="font-bold text-brand-navy">Menunggu Persetujuan ({{ $pending->count() }})</h3></div>
                <div class="divide-y divide-slate-100">
                    @forelse ($pending as $req)
                        <div class="px-5 py-4 flex items-center justify-between gap-3">
                            <div>
                                <p class="text-sm font-semibold text-brand-navy">{{ $req->personil?->name }} · <span class="text-slate-500">{{ $req->type }}</span></p>
                                <p class="text-[11px] text-slate-400">{{ tgl($req->start_date) }} – {{ tgl($req->end_date) }}</p>
                                <p class="text-xs text-slate-500 mt-1">{{ $req->reason }}</p>
                            </div>
                            <div class="flex gap-2 shrink-0">
                                <form method="POST" action="{{ route('app.leaves.approve', $req) }}"><x-csrf-button class="bg-emerald-500 hover:bg-emerald-600"><i class="ri-check-line"></i></x-csrf-button></form>
                                <form method="POST" action="{{ route('app.leaves.reject', $req) }}"><x-csrf-button class="bg-red-500 hover:bg-red-600"><i class="ri-close-line"></i></x-csrf-button></form>
                            </div>
                        </div>
                    @empty
                        <div class="px-5 py-8 text-center text-slate-400 text-sm">Tidak ada pengajuan menunggu.</div>
                    @endforelse
                </div>
            </div>
        @endif

        {{-- Riwayat pengajuan saya --}}
        @can('leave_apply')
            <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100"><h3 class="font-bold text-brand-navy">Pengajuan Saya</h3></div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-slate-50 text-slate-500 text-xs uppercase">
                            <tr><th class="text-left px-5 py-3">Jenis</th><th class="text-left px-5 py-3">Periode</th><th class="text-left px-5 py-3">Status</th></tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($myRequests as $req)
                                <tr>
                                    <td class="px-5 py-3 font-semibold text-slate-700">{{ $req->type }}</td>
                                    <td class="px-5 py-3 text-slate-500">{{ tgl($req->start_date) }} – {{ tgl($req->end_date) }}</td>
                                    <td class="px-5 py-3"><span class="text-[10px] font-bold px-2 py-0.5 rounded-full {{ $badge($req->status) }}">{{ $req->status }}</span></td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="px-5 py-8 text-center text-slate-400">Belum ada pengajuan.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endcan
    </div>
</div>
@endsection
