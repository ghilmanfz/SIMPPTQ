@extends('layouts.app')
@section('title', 'Rekap Presensi Personil')

@section('content')
<div class="space-y-5">
    <form method="GET" class="bg-white rounded-2xl border border-slate-200 p-4 flex items-end gap-3">
        <div>
            <label class="text-xs font-bold text-slate-500 uppercase">Tanggal</label>
            <input type="date" name="date" value="{{ $date->toDateString() }}" class="mt-1 rounded-xl border border-slate-200 px-3 py-2.5 text-sm">
        </div>
        <button class="rounded-xl bg-brand-navy text-white px-5 py-2.5 text-sm font-bold hover:bg-brand-navy-dark">Tampilkan</button>
        <a href="{{ route('app.presensi.rekap.export', ['date' => $date->toDateString()]) }}" class="ml-auto rounded-xl bg-brand-green text-white px-4 py-2.5 text-sm font-bold hover:bg-brand-green-dark flex items-center gap-1.5"><i class="ri-file-excel-2-line"></i> Download Excel</a>
    </form>

    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
            <h3 class="font-bold text-brand-navy">Presensi {{ tgl($date, true) }}</h3>
            <button onclick="window.print()" class="text-xs font-bold text-brand-green hover:underline"><i class="ri-printer-line"></i> Cetak</button>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-slate-500 text-xs uppercase">
                    <tr><th class="text-left px-5 py-3">Personil</th><th class="text-left px-5 py-3">Masuk</th><th class="text-left px-5 py-3">Pulang</th><th class="text-left px-5 py-3">Lokasi</th><th class="text-left px-5 py-3">Status</th></tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($logs as $log)
                        <tr>
                            <td class="px-5 py-3 font-semibold text-slate-700">{{ $log->personil?->name }}</td>
                            <td class="px-5 py-3">{{ jam($log->check_in_time) }}</td>
                            <td class="px-5 py-3">{{ $log->check_out_time ? jam($log->check_out_time) : '-' }}</td>
                            <td class="px-5 py-3 text-slate-500">{{ $log->lokasi?->name ?? '-' }}</td>
                            <td class="px-5 py-3"><span class="text-[10px] font-bold px-2 py-0.5 rounded-full {{ $log->status === 'Terlambat' ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700' }}">{{ $log->status }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-5 py-8 text-center text-slate-400">Tidak ada data presensi pada tanggal ini.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-5 py-3">{{ $logs->links() }}</div>
    </div>
</div>
@endsection
