@extends('layouts.app')
@section('title', 'Tukar Jam Mengajar')

@php
    $badge = fn ($s) => match ($s) {
        'Diterapkan', 'Disetujui' => 'bg-emerald-100 text-emerald-700',
        'Ditolak', 'Dibatalkan' => 'bg-red-100 text-red-600',
        default => 'bg-amber-100 text-amber-700',
    };
    $me = auth()->user();
    $canApply = $me->can('swap_apply') && $me->isPengajar();
@endphp

@section('content')
<div class="grid lg:grid-cols-3 gap-6">
    @if ($canApply)
        <div class="lg:col-span-1">
            <div class="bg-white rounded-2xl border border-slate-200 p-5">
                <h3 class="font-bold text-brand-navy mb-4">Ajukan Tukar Jam</h3>
                @if ($mySchedules->isEmpty())
                    <p class="text-sm text-slate-400">Anda belum memiliki jadwal mengajar.</p>
                @else
                    <form method="POST" action="{{ route('app.swaps.store') }}" class="space-y-3">
                        @csrf
                        <div>
                            <label class="text-xs font-bold text-slate-500 uppercase">Jadwal</label>
                            <select name="jadwal_id" required class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm">
                                @foreach ($mySchedules as $s)
                                    <option value="{{ $s->id }}">{{ $s->day }} · {{ $s->sesi?->name }} — {{ $s->mapel?->name ?? 'Halaqah' }} ({{ $s->kelas?->name }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="text-xs font-bold text-slate-500 uppercase">Tanggal</label>
                            <input type="date" name="date" required class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm">
                        </div>
                        <div>
                            <label class="text-xs font-bold text-slate-500 uppercase">Guru Pengganti</label>
                            <select name="target_personil_id" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm">
                                <option value="">— Belum ditentukan —</option>
                                @foreach ($teachers as $t)
                                    <option value="{{ $t->id }}">{{ $t->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="text-xs font-bold text-slate-500 uppercase">Alasan</label>
                            <textarea name="reason" rows="3" required class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm"></textarea>
                        </div>
                        <button class="w-full rounded-xl bg-brand-navy text-white px-4 py-2.5 text-sm font-bold hover:bg-brand-navy-dark">Kirim Pengajuan</button>
                    </form>
                @endif
            </div>
        </div>
    @endif

    <div class="{{ $canApply ? 'lg:col-span-2' : 'lg:col-span-3' }} space-y-6">
        @if ($canApprove)
            <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100"><h3 class="font-bold text-brand-navy">Menunggu Persetujuan ({{ $pending->count() }})</h3></div>
                <div class="divide-y divide-slate-100">
                    @forelse ($pending as $req)
                        <div class="px-5 py-4 flex items-center justify-between gap-3">
                            <div>
                                <p class="text-sm font-semibold text-brand-navy">{{ $req->requester?->name }}</p>
                                <p class="text-[11px] text-slate-400">{{ $req->jadwal?->day }} · {{ $req->jadwal?->mapel?->name ?? 'Halaqah' }} ({{ $req->jadwal?->kelas?->name }}) — {{ tgl($req->date) }}</p>
                                <p class="text-xs text-slate-500 mt-1">Pengganti: {{ $req->target?->name ?? 'Belum ditentukan' }} · {{ $req->reason }}</p>
                            </div>
                            <div class="flex gap-2 shrink-0">
                                <form method="POST" action="{{ route('app.swaps.approve', $req) }}"><x-csrf-button class="bg-emerald-500 hover:bg-emerald-600"><i class="ri-check-line"></i></x-csrf-button></form>
                                <form method="POST" action="{{ route('app.swaps.reject', $req) }}"><x-csrf-button class="bg-red-500 hover:bg-red-600"><i class="ri-close-line"></i></x-csrf-button></form>
                            </div>
                        </div>
                    @empty
                        <div class="px-5 py-8 text-center text-slate-400 text-sm">Tidak ada pengajuan menunggu.</div>
                    @endforelse
                </div>
            </div>
        @endif

        @if ($canApply)
            <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100"><h3 class="font-bold text-brand-navy">Pengajuan Saya</h3></div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-slate-50 text-slate-500 text-xs uppercase">
                            <tr><th class="text-left px-5 py-3">Jadwal</th><th class="text-left px-5 py-3">Tanggal</th><th class="text-left px-5 py-3">Pengganti</th><th class="text-left px-5 py-3">Status</th></tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($myRequests as $req)
                                <tr>
                                    <td class="px-5 py-3 font-semibold text-slate-700">{{ $req->jadwal?->mapel?->name ?? 'Halaqah' }} ({{ $req->jadwal?->kelas?->name }})</td>
                                    <td class="px-5 py-3 text-slate-500">{{ tgl($req->date) }}</td>
                                    <td class="px-5 py-3 text-slate-500">{{ $req->target?->name ?? '-' }}</td>
                                    <td class="px-5 py-3"><span class="text-[10px] font-bold px-2 py-0.5 rounded-full {{ $badge($req->status) }}">{{ $req->status }}</span></td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="px-5 py-8 text-center text-slate-400">Belum ada pengajuan.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
