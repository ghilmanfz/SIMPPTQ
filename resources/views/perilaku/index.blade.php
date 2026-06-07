@extends('layouts.app')
@section('title', 'Perilaku & Poin Santri')

@section('content')
<div class="grid lg:grid-cols-3 gap-6">
    <div class="lg:col-span-1">
        <div class="bg-white rounded-2xl border border-slate-200 p-5">
            <h3 class="font-bold text-brand-navy mb-4">Catat Perilaku</h3>
            <form method="POST" action="{{ route('app.behaviors.store') }}" class="space-y-3">
                @csrf
                <div>
                    <label class="text-xs font-bold text-slate-500 uppercase">Santri</label>
                    <select name="santri_id" required class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm">
                        <option value="">— Pilih —</option>
                        @foreach ($santriList as $s)<option value="{{ $s->id }}">{{ $s->name }} ({{ $s->kelas?->name }})</option>@endforeach
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="text-xs font-bold text-slate-500 uppercase">Jenis</label>
                        <select name="type" required class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm"><option>Kebaikan</option><option>Pelanggaran</option></select>
                    </div>
                    <div>
                        <label class="text-xs font-bold text-slate-500 uppercase">Poin</label>
                        <input type="number" name="points" value="5" min="0" required class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm">
                    </div>
                </div>
                <div>
                    <label class="text-xs font-bold text-slate-500 uppercase">Kategori</label>
                    <input name="category" placeholder="Kedisiplinan, Ibadah, ..." class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm">
                </div>
                <div>
                    <label class="text-xs font-bold text-slate-500 uppercase">Tanggal</label>
                    <input type="date" name="date" value="{{ now()->toDateString() }}" required class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm">
                </div>
                <div>
                    <label class="text-xs font-bold text-slate-500 uppercase">Keterangan</label>
                    <textarea name="note" rows="2" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm"></textarea>
                </div>
                <button class="w-full rounded-xl bg-brand-navy text-white px-4 py-2.5 text-sm font-bold hover:bg-brand-navy-dark">Simpan Catatan</button>
            </form>
        </div>
    </div>

    <div class="lg:col-span-2">
        <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100"><h3 class="font-bold text-brand-navy">Riwayat Perilaku</h3></div>
            <div class="divide-y divide-slate-100">
                @forelse ($behaviors as $b)
                    <div class="px-5 py-3 flex items-start justify-between gap-3">
                        <div class="flex items-start gap-3">
                            <span class="h-9 w-9 rounded-xl flex items-center justify-center {{ $b->type === 'Kebaikan' ? 'bg-emerald-100 text-emerald-600' : 'bg-red-100 text-red-500' }}"><i class="{{ $b->type === 'Kebaikan' ? 'ri-thumb-up-line' : 'ri-error-warning-line' }}"></i></span>
                            <div>
                                <p class="text-sm font-semibold text-brand-navy">{{ $b->santri?->name }} <span class="text-xs font-normal text-slate-400">· {{ $b->category }}</span></p>
                                <p class="text-xs text-slate-500">{{ $b->note }}</p>
                                <p class="text-[11px] text-slate-400 mt-0.5">{{ tgl($b->date) }} · {{ $b->recorder?->name }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            <span class="text-sm font-bold {{ $b->type === 'Kebaikan' ? 'text-emerald-600' : 'text-red-500' }}">{{ $b->type === 'Kebaikan' ? '+' : '-' }}{{ $b->points }}</span>
                            <form method="POST" action="{{ route('app.behaviors.destroy', $b) }}" data-confirm="Hapus catatan perilaku ini?" data-confirm-title="Hapus Perilaku" data-confirm-danger>@csrf @method('DELETE')<button class="text-slate-300 hover:text-red-500"><i class="ri-delete-bin-line"></i></button></form>
                        </div>
                    </div>
                @empty
                    <div class="px-5 py-10 text-center text-slate-400">Belum ada catatan perilaku.</div>
                @endforelse
            </div>
            <div class="px-5 py-3">{{ $behaviors->links() }}</div>
        </div>
    </div>
</div>
@endsection
