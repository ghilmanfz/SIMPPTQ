@extends('layouts.app')
@section('title', 'Nilai & Perkembangan')

@section('content')
<div class="grid lg:grid-cols-3 gap-6">
    <div class="lg:col-span-1">
        <div class="bg-white rounded-2xl border border-slate-200 p-5">
            <h3 class="font-bold text-brand-navy mb-4">Input Nilai / Perkembangan</h3>
            <form method="POST" action="{{ route('app.grades.store') }}" class="space-y-3">
                @csrf
                <div>
                    <label class="text-xs font-bold text-slate-500 uppercase">Santri</label>
                    <select name="santri_id" required class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm">
                        <option value="">— Pilih —</option>
                        @foreach ($santriList as $s)<option value="{{ $s->id }}">{{ $s->name }} ({{ $s->kelas?->name }})</option>@endforeach
                    </select>
                </div>
                <div>
                    <label class="text-xs font-bold text-slate-500 uppercase">Mapel / Halaqah</label>
                    <select name="mapel_id" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm">
                        <option value="">— Umum —</option>
                        @foreach ($mapels as $m)<option value="{{ $m->id }}">{{ $m->name }}</option>@endforeach
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="text-xs font-bold text-slate-500 uppercase">Topik</label>
                        <input name="subject" placeholder="Juz 29..." class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm">
                    </div>
                    <div>
                        <label class="text-xs font-bold text-slate-500 uppercase">Nilai</label>
                        <input type="number" name="score" min="0" max="100" step="0.5" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm">
                    </div>
                </div>
                <div>
                    <label class="text-xs font-bold text-slate-500 uppercase">Tanggal</label>
                    <input type="date" name="date" value="{{ now()->toDateString() }}" required class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm">
                </div>
                <div>
                    <label class="text-xs font-bold text-slate-500 uppercase">Catatan</label>
                    <textarea name="note" rows="2" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm"></textarea>
                </div>
                <button class="w-full rounded-xl bg-brand-navy text-white px-4 py-2.5 text-sm font-bold hover:bg-brand-navy-dark">Simpan Nilai</button>
            </form>
        </div>
    </div>

    <div class="lg:col-span-2">
        <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100"><h3 class="font-bold text-brand-navy">Riwayat Nilai</h3></div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 text-slate-500 text-xs uppercase">
                        <tr><th class="text-left px-5 py-3">Santri</th><th class="text-left px-5 py-3">Mapel/Topik</th><th class="text-left px-5 py-3">Nilai</th><th class="text-left px-5 py-3">Tanggal</th><th class="px-3"></th></tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($grades as $g)
                            <tr>
                                <td class="px-5 py-3 font-semibold text-brand-navy">{{ $g->santri?->name }}</td>
                                <td class="px-5 py-3 text-slate-600">{{ $g->mapel?->name ?? $g->subject ?? '-' }}<p class="text-[11px] text-slate-400">{{ $g->note }}</p></td>
                                <td class="px-5 py-3"><span class="font-bold text-brand-green">{{ $g->score !== null ? rtrim(rtrim(number_format($g->score,1),'0'),'.') : '-' }}</span></td>
                                <td class="px-5 py-3 text-slate-500">{{ tgl($g->date) }}</td>
                                <td class="px-3 py-3 text-right"><form method="POST" action="{{ route('app.grades.destroy', $g) }}" data-confirm="Hapus nilai ini?" data-confirm-title="Hapus Nilai" data-confirm-danger>@csrf @method('DELETE')<button class="text-slate-300 hover:text-red-500"><i class="ri-delete-bin-line"></i></button></form></td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-5 py-10 text-center text-slate-400">Belum ada data nilai.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-5 py-3">{{ $grades->links() }}</div>
        </div>
    </div>
</div>
@endsection
