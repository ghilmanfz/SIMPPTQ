@extends('layouts.app')
@section('title', 'Jadwal Mengajar')

@section('content')
<div x-data="jadwalModule()" class="space-y-5">
    <div class="flex flex-wrap justify-end gap-2">
        <a href="{{ route('app.jadwal.today') }}" class="rounded-xl bg-brand-sky text-brand-navy px-4 py-2.5 text-sm font-bold hover:bg-brand-navy hover:text-white transition-colors flex items-center gap-2"><i class="ri-calendar-todo-line"></i> Jadwal Hari Ini</a>
        @if ($canManage)
            <button @click="openCreate()" class="rounded-xl bg-brand-navy text-white px-4 py-2.5 text-sm font-bold hover:bg-brand-navy-dark flex items-center gap-2"><i class="ri-add-line"></i> Tambah Jadwal</button>
        @endif
    </div>

    <div class="grid md:grid-cols-2 xl:grid-cols-3 gap-4">
        @foreach ($days as $day)
            @php($items = $jadwals->get($day))
            <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
                <div class="px-4 py-3 bg-brand-navy text-white text-sm font-bold">{{ $day }}</div>
                <div class="divide-y divide-slate-100">
                    @forelse ($items ?? [] as $j)
                        <div class="px-4 py-3 flex items-start justify-between gap-2">
                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-brand-navy truncate">{{ $j->mapel?->name ?? 'Halaqah' }}</p>
                                <p class="text-[11px] text-slate-500">{{ $j->kelas?->name }} · {{ jam($j->start_time) }}–{{ jam($j->end_time) }}</p>
                                <p class="text-[11px] text-slate-400">{{ $j->personil?->name }}</p>
                            </div>
                            @if ($canManage)
                                <div class="flex gap-1 shrink-0">
                                    <button @click='openEdit(@json($j))' class="h-7 w-7 rounded-lg bg-slate-100 text-slate-600 hover:bg-brand-sky text-xs"><i class="ri-pencil-line"></i></button>
                                    <form method="POST" action="{{ route('app.jadwal.destroy', $j) }}" data-confirm="Hapus jadwal mengajar ini?" data-confirm-title="Hapus Jadwal" data-confirm-danger>
                                        @csrf @method('DELETE')
                                        <button class="h-7 w-7 rounded-lg bg-red-50 text-red-500 hover:bg-red-100 text-xs"><i class="ri-delete-bin-line"></i></button>
                                    </form>
                                </div>
                            @endif
                        </div>
                    @empty
                        <div class="px-4 py-6 text-center text-xs text-slate-300">Kosong</div>
                    @endforelse
                </div>
            </div>
        @endforeach
    </div>

    @if ($canManage)
        <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div @click="open=false" class="absolute inset-0 bg-black/50"></div>
            <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-lg p-6">
                <h3 class="font-bold text-brand-navy mb-4" x-text="mode==='create' ? 'Tambah Jadwal' : 'Edit Jadwal'"></h3>
                <form :action="actionUrl" method="POST" class="space-y-3">
                    @csrf
                    <template x-if="mode==='edit'"><input type="hidden" name="_method" value="PUT"></template>
                    <div>
                        <label class="text-xs font-bold text-slate-500 uppercase">Pengajar</label>
                        <select name="personil_id" x-model="form.personil_id" required class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm">
                            <option value="">— Pilih —</option>
                            @foreach ($pengajar as $p)<option value="{{ $p->id }}">{{ $p->name }}</option>@endforeach
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="text-xs font-bold text-slate-500 uppercase">Kelas</label>
                            <select name="kelas_id" x-model="form.kelas_id" required class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm">
                                <option value="">— Pilih —</option>
                                @foreach ($kelasList as $k)<option value="{{ $k->id }}">{{ $k->name }}</option>@endforeach
                            </select>
                        </div>
                        <div>
                            <label class="text-xs font-bold text-slate-500 uppercase">Mapel/Halaqah</label>
                            <select name="mapel_id" x-model="form.mapel_id" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm">
                                <option value="">— Pilih —</option>
                                @foreach ($mapels as $m)<option value="{{ $m->id }}">{{ $m->name }}</option>@endforeach
                            </select>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="text-xs font-bold text-slate-500 uppercase">Hari</label>
                            <select name="day" x-model="form.day" required class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm">
                                @foreach ($days as $d)<option value="{{ $d }}">{{ $d }}</option>@endforeach
                            </select>
                        </div>
                        <div>
                            <label class="text-xs font-bold text-slate-500 uppercase">Sesi</label>
                            <select name="sesi_id" x-model="form.sesi_id" required class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm">
                                <option value="">— Pilih —</option>
                                @foreach ($sesis as $s)<option value="{{ $s->id }}">{{ $s->name }} ({{ jam($s->start_time) }}–{{ jam($s->end_time) }})</option>@endforeach
                            </select>
                        </div>
                    </div>
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
    function jadwalModule() {
        return {
            open: false, mode: 'create',
            form: { id: null, personil_id: '', kelas_id: '', mapel_id: '', day: 'Senin', sesi_id: '' },
            get actionUrl() { return this.mode === 'create' ? '{{ route('app.jadwal.store') }}' : '{{ url('app/jadwal') }}/' + this.form.id; },
            openCreate() { this.mode = 'create'; this.form = { id: null, personil_id: '', kelas_id: '', mapel_id: '', day: 'Senin', sesi_id: '' }; this.open = true; },
            openEdit(j) { this.mode = 'edit'; this.form = { id: j.id, personil_id: j.personil_id, kelas_id: j.kelas_id, mapel_id: j.mapel_id ?? '', day: j.day, sesi_id: j.sesi_id ?? '' }; this.open = true; },
        };
    }
</script>
@endpush
