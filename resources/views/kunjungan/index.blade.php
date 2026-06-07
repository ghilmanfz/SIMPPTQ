@extends('layouts.app')
@section('title', 'Kunjungan Wali Santri')

@section('content')
<div class="grid lg:grid-cols-3 gap-6">
    <div class="lg:col-span-1">
        <div class="bg-white rounded-2xl border border-slate-200 p-5">
            <h3 class="font-bold text-brand-navy mb-4">Catat Kunjungan</h3>
            <form method="POST" action="{{ route('app.visits.store') }}" class="space-y-3">
                @csrf
                <div>
                    <label class="text-xs font-bold text-slate-500 uppercase">Santri</label>
                    <select name="santri_id" required class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm">
                        <option value="">— Pilih —</option>
                        @foreach ($santriList as $s)<option value="{{ $s->id }}">{{ $s->name }} ({{ $s->kelas?->name }})</option>@endforeach
                    </select>
                </div>
                <div>
                    <label class="text-xs font-bold text-slate-500 uppercase">Nama Pengunjung</label>
                    <input name="visitor_name" required class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm">
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="text-xs font-bold text-slate-500 uppercase">Hubungan</label>
                        <input name="relation" placeholder="Ayah, Ibu..." class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm">
                    </div>
                    <div>
                        <label class="text-xs font-bold text-slate-500 uppercase">Waktu</label>
                        <input type="datetime-local" name="visit_at" value="{{ now()->format('Y-m-d\TH:i') }}" required class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm">
                    </div>
                </div>
                <div>
                    <label class="text-xs font-bold text-slate-500 uppercase">Keperluan</label>
                    <input name="purpose" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm">
                </div>
                <div>
                    <label class="text-xs font-bold text-slate-500 uppercase">Catatan</label>
                    <textarea name="note" rows="2" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm"></textarea>
                </div>
                <button class="w-full rounded-xl bg-brand-navy text-white px-4 py-2.5 text-sm font-bold hover:bg-brand-navy-dark">Simpan Kunjungan</button>
            </form>
        </div>
    </div>

    <div class="lg:col-span-2">
        <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100"><h3 class="font-bold text-brand-navy">Riwayat Kunjungan</h3></div>
            <div class="divide-y divide-slate-100">
                @forelse ($visits as $v)
                    <div class="px-5 py-3 flex items-start justify-between gap-3">
                        <div class="flex items-start gap-3">
                            <span class="h-9 w-9 rounded-xl bg-brand-sky text-brand-navy flex items-center justify-center"><i class="ri-parent-line"></i></span>
                            <div>
                                <p class="text-sm font-semibold text-brand-navy">{{ $v->visitor_name }} <span class="text-xs font-normal text-slate-400">({{ $v->relation }})</span></p>
                                <p class="text-xs text-slate-500">Menjenguk: {{ $v->santri?->name }} · {{ $v->purpose }}</p>
                                <p class="text-[11px] text-slate-400 mt-0.5">{{ tgl($v->visit_at) }} {{ jam($v->visit_at) }} · {{ $v->note }}</p>
                            </div>
                        </div>
                        <form method="POST" action="{{ route('app.visits.destroy', $v) }}" data-confirm="Hapus catatan kunjungan ini?" data-confirm-title="Hapus Kunjungan" data-confirm-danger>@csrf @method('DELETE')<button class="text-slate-300 hover:text-red-500 shrink-0"><i class="ri-delete-bin-line"></i></button></form>
                    </div>
                @empty
                    <div class="px-5 py-10 text-center text-slate-400">Belum ada kunjungan tercatat.</div>
                @endforelse
            </div>
            <div class="px-5 py-3">{{ $visits->links() }}</div>
        </div>
    </div>
</div>
@endsection
