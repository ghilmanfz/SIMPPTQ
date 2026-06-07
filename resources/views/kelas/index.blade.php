@extends('layouts.app')
@section('title', 'Kelas / Rombel')

@section('content')
<div x-data="kelasModule()" class="space-y-5">
    @if ($canManage)
        <div class="flex flex-wrap justify-end gap-2">
            <a href="{{ route('app.kelas.promote') }}" class="rounded-xl bg-amber-400 text-brand-navy px-4 py-2.5 text-sm font-bold hover:bg-amber-300 flex items-center gap-2"><i class="ri-arrow-up-double-line"></i> Naik Kelas</a>
            <button @click="openCreate()" class="rounded-xl bg-brand-navy text-white px-4 py-2.5 text-sm font-bold hover:bg-brand-navy-dark flex items-center gap-2"><i class="ri-add-line"></i> Tambah Kelas</button>
        </div>
    @endif

    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse ($kelas as $k)
            <div class="bg-white rounded-2xl border border-slate-200 p-5">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-lg font-bold text-brand-navy">{{ $k->name }}</p>
                        <p class="text-xs text-slate-400">Tingkat {{ $k->tingkat ?? '-' }} · {{ $k->tahunAjaran?->name ?? 'Tanpa TA' }}</p>
                    </div>
                    <span class="h-10 w-10 rounded-xl bg-brand-sky text-brand-navy flex items-center justify-center font-bold">{{ $k->santris_count }}</span>
                </div>
                <p class="text-xs text-slate-500 mt-3"><i class="ri-user-star-line"></i> Wali: {{ $k->waliKelas?->name ?? 'Belum ada' }}</p>
                <div class="mt-4 pt-3 border-t border-slate-100 space-y-2">
                    <a href="{{ route('app.kelas.members', $k) }}" class="flex items-center justify-center gap-1.5 rounded-lg bg-brand-sky text-brand-navy text-xs font-bold py-2 hover:bg-brand-navy hover:text-white transition-colors"><i class="ri-group-line"></i> Kelola Santri</a>
                    @if ($canManage)
                        <div class="flex gap-2">
                            <button @click='openEdit(@json($k))' class="flex-1 rounded-lg bg-slate-100 text-slate-600 text-xs font-semibold py-2 hover:bg-brand-sky">Edit</button>
                            <form method="POST" action="{{ route('app.kelas.destroy', $k) }}" data-confirm="Hapus kelas ini? Santri di kelas ini bisa terpengaruh." data-confirm-title="Hapus Kelas" data-confirm-danger class="flex-1">@csrf @method('DELETE')<button class="w-full rounded-lg bg-red-50 text-red-500 text-xs font-semibold py-2 hover:bg-red-100">Hapus</button></form>
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <div class="sm:col-span-2 lg:col-span-3 bg-white rounded-2xl border border-dashed border-slate-300 p-10 text-center text-slate-400">Belum ada kelas.</div>
        @endforelse
    </div>

    @if ($canManage)
        <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div @click="open=false" class="absolute inset-0 bg-black/50"></div>
            <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-md p-6">
                <h3 class="font-bold text-brand-navy mb-4" x-text="mode==='create' ? 'Tambah Kelas' : 'Edit Kelas'"></h3>
                <form :action="actionUrl" method="POST" class="space-y-3">
                    @csrf
                    <template x-if="mode==='edit'"><input type="hidden" name="_method" value="PUT"></template>
                    <div class="grid grid-cols-2 gap-3">
                        <div><label class="text-xs font-bold text-slate-500 uppercase">Nama</label><input name="name" x-model="form.name" required class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm"></div>
                        <div><label class="text-xs font-bold text-slate-500 uppercase">Tingkat</label><input name="tingkat" x-model="form.tingkat" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm"></div>
                    </div>
                    <div><label class="text-xs font-bold text-slate-500 uppercase">Tahun Ajaran</label><select name="tahun_ajaran_id" x-model="form.tahun_ajaran_id" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm"><option value="">-</option>@foreach ($tahunAjarans as $ta)<option value="{{ $ta->id }}">{{ $ta->name }}</option>@endforeach</select></div>
                    <div><label class="text-xs font-bold text-slate-500 uppercase">Wali Kelas</label><select name="wali_kelas_id" x-model="form.wali_kelas_id" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm"><option value="">-</option>@foreach ($waliOptions as $w)<option value="{{ $w->id }}">{{ $w->name }}</option>@endforeach</select></div>
                    <label class="flex items-center gap-2 text-sm text-slate-600"><input type="checkbox" name="is_active" value="1" x-model="form.is_active" class="rounded border-slate-300 text-brand-navy"> Aktif</label>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" @click="open=false" class="rounded-xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600">Batal</button>
                        <button class="rounded-xl bg-brand-navy text-white px-5 py-2 text-sm font-bold hover:bg-brand-navy-dark">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
    function kelasModule() {
        return {
            open: false, mode: 'create',
            form: { id: null, name: '', tingkat: '', tahun_ajaran_id: '', wali_kelas_id: '', is_active: true },
            get actionUrl() { return this.mode === 'create' ? '{{ route('app.kelas.store') }}' : '{{ url('app/kelas') }}/' + this.form.id; },
            openCreate() { this.mode = 'create'; this.form = { id: null, name: '', tingkat: '', tahun_ajaran_id: '', wali_kelas_id: '', is_active: true }; this.open = true; },
            openEdit(k) { this.mode = 'edit'; this.form = { id: k.id, name: k.name, tingkat: k.tingkat ?? '', tahun_ajaran_id: k.tahun_ajaran_id ?? '', wali_kelas_id: k.wali_kelas_id ?? '', is_active: !!k.is_active }; this.open = true; },
        };
    }
</script>
@endpush
