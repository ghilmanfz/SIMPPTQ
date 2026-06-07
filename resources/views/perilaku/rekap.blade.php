@extends('layouts.app')
@section('title', 'Rekap & Peringkat Perilaku')

@section('content')
<div class="space-y-5">
    <a href="{{ route('app.behaviors.index') }}" class="text-xs font-bold text-slate-500 hover:text-brand-navy"><i class="ri-arrow-left-line"></i> Kembali ke Catat Perilaku</a>

    {{-- Filter rentang --}}
    <form method="GET" class="bg-white rounded-2xl border border-slate-200 p-4 flex flex-wrap items-end gap-3">
        <div>
            <label class="text-xs font-bold text-slate-500 uppercase">Dari</label>
            <input type="date" name="start" value="{{ $start->toDateString() }}" class="mt-1 rounded-xl border border-slate-200 px-3 py-2.5 text-sm">
        </div>
        <div>
            <label class="text-xs font-bold text-slate-500 uppercase">Sampai</label>
            <input type="date" name="end" value="{{ $end->toDateString() }}" class="mt-1 rounded-xl border border-slate-200 px-3 py-2.5 text-sm">
        </div>
        <button class="rounded-xl bg-brand-navy text-white px-5 py-2.5 text-sm font-bold hover:bg-brand-navy-dark">Terapkan</button>
        <a href="{{ route('app.behaviors.export', ['start' => $start->toDateString(), 'end' => $end->toDateString()]) }}" class="ml-auto rounded-xl bg-brand-green text-white px-4 py-2.5 text-sm font-bold hover:bg-brand-green-dark flex items-center gap-1.5"><i class="ri-file-excel-2-line"></i> Download Excel</a>
    </form>

    <p class="text-xs text-slate-400">Periode: {{ tgl($start) }} – {{ tgl($end) }}</p>

    <div class="grid lg:grid-cols-3 gap-5">
        {{-- Peringkat santri --}}
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100"><h3 class="font-bold text-brand-navy">Peringkat Santri (berdasarkan saldo poin)</h3></div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 text-slate-500 text-xs uppercase">
                        <tr>
                            <th class="text-left px-5 py-3 w-12">#</th>
                            <th class="text-left px-5 py-3">Santri</th>
                            <th class="text-center px-3 py-3">Kebaikan</th>
                            <th class="text-center px-3 py-3">Pelanggaran</th>
                            <th class="text-center px-5 py-3">Saldo</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($ranking as $i => $r)
                            <tr>
                                <td class="px-5 py-3">
                                    @if ($i < 3)
                                        <span class="inline-flex h-7 w-7 items-center justify-center rounded-full font-bold text-xs {{ ['bg-amber-100 text-amber-600','bg-slate-200 text-slate-600','bg-orange-100 text-orange-600'][$i] }}">{{ $i + 1 }}</span>
                                    @else
                                        <span class="text-slate-400 font-semibold">{{ $i + 1 }}</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3">
                                    <p class="font-semibold text-brand-navy">{{ $r->name }}</p>
                                    <p class="text-[11px] text-slate-400">Kelas {{ $r->kelas?->name ?? '-' }}</p>
                                </td>
                                <td class="px-3 py-3 text-center font-bold text-emerald-600">+{{ (int) $r->kebaikan }}</td>
                                <td class="px-3 py-3 text-center font-bold text-red-500">−{{ (int) $r->pelanggaran }}</td>
                                <td class="px-5 py-3 text-center"><span class="font-extrabold {{ $r->saldo >= 0 ? 'text-blue-600' : 'text-red-500' }}">{{ $r->saldo >= 0 ? '+' : '' }}{{ (int) $r->saldo }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-5 py-10 text-center text-slate-400">Belum ada data perilaku pada periode ini.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Rekap per kategori --}}
        <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100"><h3 class="font-bold text-brand-navy">Per Kategori</h3></div>
            <div class="divide-y divide-slate-100">
                @forelse ($categories as $c)
                    <div class="px-5 py-3">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-semibold text-brand-navy">{{ $c->kategori }}</span>
                            <span class="text-[11px] text-slate-400">{{ (int) $c->jumlah }} catatan</span>
                        </div>
                        <div class="flex gap-3 mt-1 text-xs font-bold">
                            <span class="text-emerald-600">+{{ (int) $c->kebaikan }}</span>
                            <span class="text-red-500">−{{ (int) $c->pelanggaran }}</span>
                        </div>
                    </div>
                @empty
                    <div class="px-5 py-10 text-center text-slate-400 text-sm">Belum ada kategori.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
