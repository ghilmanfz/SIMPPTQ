@extends('layouts.app')
@section('title', 'Master Akademik')

@section('content')
<div x-data="{ tab: 'ta' }" class="space-y-5">
    {{-- Tabs --}}
    <div class="flex gap-2 bg-white rounded-xl border border-slate-200 p-1 w-fit">
        <button @click="tab='ta'" :class="tab==='ta' ? 'bg-brand-navy text-white' : 'text-slate-500'" class="px-4 py-2 rounded-lg text-sm font-bold">Tahun Ajaran</button>
        <button @click="tab='mapel'" :class="tab==='mapel' ? 'bg-brand-navy text-white' : 'text-slate-500'" class="px-4 py-2 rounded-lg text-sm font-bold">Mapel / Halaqah</button>
        <button @click="tab='sesi'" :class="tab==='sesi' ? 'bg-brand-navy text-white' : 'text-slate-500'" class="px-4 py-2 rounded-lg text-sm font-bold">Sesi</button>
    </div>

    {{-- Tahun Ajaran --}}
    <div x-show="tab==='ta'" class="grid lg:grid-cols-3 gap-5">
        <div class="bg-white rounded-2xl border border-slate-200 p-5">
            <h3 class="font-bold text-brand-navy mb-3">Tambah Tahun Ajaran</h3>
            <form method="POST" action="{{ route('app.tahun-ajaran.store') }}" class="space-y-3">
                @csrf
                <input name="name" placeholder="2026/2027 Ganjil" required class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
                <div class="grid grid-cols-2 gap-2">
                    <input type="date" name="start_date" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                    <input type="date" name="end_date" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                </div>
                <label class="flex items-center gap-2 text-sm text-slate-600"><input type="checkbox" name="is_active" value="1" class="rounded border-slate-300 text-brand-navy"> Jadikan aktif</label>
                <button class="w-full rounded-xl bg-brand-navy text-white px-4 py-2 text-sm font-bold hover:bg-brand-navy-dark">Simpan</button>
            </form>
        </div>
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 divide-y divide-slate-100">
            @forelse ($tahunAjarans as $ta)
                <div class="px-5 py-3 flex items-center justify-between">
                    <div>
                        <p class="font-semibold text-brand-navy">{{ $ta->name }} @if ($ta->is_active)<span class="ml-2 text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700">Aktif</span>@endif</p>
                        <p class="text-[11px] text-slate-400">{{ tgl($ta->start_date) }} – {{ tgl($ta->end_date) }}</p>
                    </div>
                    <div class="flex gap-2">
                        @unless ($ta->is_active)
                            <form method="POST" action="{{ route('app.tahun-ajaran.update', $ta) }}">@csrf @method('PUT')<input type="hidden" name="name" value="{{ $ta->name }}"><input type="hidden" name="is_active" value="1"><button class="text-xs font-bold text-brand-green hover:underline">Aktifkan</button></form>
                        @endunless
                        <form method="POST" action="{{ route('app.tahun-ajaran.destroy', $ta) }}" data-confirm="Hapus data ini? Tindakan ini tidak dapat dibatalkan." data-confirm-title="Konfirmasi Hapus" data-confirm-danger>@csrf @method('DELETE')<button class="text-xs font-bold text-red-500 hover:underline">Hapus</button></form>
                    </div>
                </div>
            @empty
                <div class="px-5 py-8 text-center text-slate-400 text-sm">Belum ada tahun ajaran.</div>
            @endforelse
        </div>
    </div>

    {{-- Mapel --}}
    <div x-show="tab==='mapel'" x-cloak class="grid lg:grid-cols-3 gap-5">
        <div class="bg-white rounded-2xl border border-slate-200 p-5">
            <h3 class="font-bold text-brand-navy mb-3">Tambah Mapel / Halaqah</h3>
            <form method="POST" action="{{ route('app.mapel.store') }}" class="space-y-3">
                @csrf
                <input name="name" placeholder="Nama mapel/halaqah" required class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
                <input name="code" placeholder="Kode (opsional)" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
                <select name="type" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm"><option value="Halaqah">Halaqah</option><option value="Mapel">Mapel</option></select>
                <button class="w-full rounded-xl bg-brand-navy text-white px-4 py-2 text-sm font-bold hover:bg-brand-navy-dark">Simpan</button>
            </form>
        </div>
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 divide-y divide-slate-100">
            @forelse ($mapels as $m)
                <div class="px-5 py-3 flex items-center justify-between">
                    <div><p class="font-semibold text-brand-navy">{{ $m->name }}</p><p class="text-[11px] text-slate-400">{{ $m->type }} @if($m->code) · {{ $m->code }} @endif</p></div>
                    <form method="POST" action="{{ route('app.mapel.destroy', $m) }}" data-confirm="Hapus data ini? Tindakan ini tidak dapat dibatalkan." data-confirm-title="Konfirmasi Hapus" data-confirm-danger>@csrf @method('DELETE')<button class="text-xs font-bold text-red-500 hover:underline">Hapus</button></form>
                </div>
            @empty
                <div class="px-5 py-8 text-center text-slate-400 text-sm">Belum ada mapel.</div>
            @endforelse
        </div>
    </div>

    {{-- Sesi --}}
    <div x-show="tab==='sesi'" x-cloak class="grid lg:grid-cols-3 gap-5">
        <div class="bg-white rounded-2xl border border-slate-200 p-5">
            <h3 class="font-bold text-brand-navy mb-3">Tambah Sesi</h3>
            <form method="POST" action="{{ route('app.sesi.store') }}" class="space-y-3">
                @csrf
                <input name="name" placeholder="Nama sesi" required class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
                <div class="grid grid-cols-2 gap-2">
                    <input type="time" name="start_time" required class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                    <input type="time" name="end_time" required class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                </div>
                <input type="number" name="order" placeholder="Urutan" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
                <button class="w-full rounded-xl bg-brand-navy text-white px-4 py-2 text-sm font-bold hover:bg-brand-navy-dark">Simpan</button>
            </form>
        </div>
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 divide-y divide-slate-100">
            @forelse ($sesis as $s)
                <div class="px-5 py-3 flex items-center justify-between">
                    <div><p class="font-semibold text-brand-navy">{{ $s->name }}</p><p class="text-[11px] text-slate-400">{{ jam($s->start_time) }} – {{ jam($s->end_time) }}</p></div>
                    <form method="POST" action="{{ route('app.sesi.destroy', $s) }}" data-confirm="Hapus data ini? Tindakan ini tidak dapat dibatalkan." data-confirm-title="Konfirmasi Hapus" data-confirm-danger>@csrf @method('DELETE')<button class="text-xs font-bold text-red-500 hover:underline">Hapus</button></form>
                </div>
            @empty
                <div class="px-5 py-8 text-center text-slate-400 text-sm">Belum ada sesi.</div>
            @endforelse
        </div>
    </div>
</div>
@endsection
