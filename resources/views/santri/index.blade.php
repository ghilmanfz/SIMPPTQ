@extends('layouts.app')
@section('title', 'Data Santri')

@php
    $statusBadge = fn ($s) => match ($s) {
        'Aktif' => 'bg-emerald-100 text-emerald-700',
        'Lulus' => 'bg-blue-100 text-blue-700',
        default => 'bg-slate-100 text-slate-500',
    };
@endphp

@section('content')
<div x-data="santriModule()" class="space-y-5">
    {{-- Filter + aksi --}}
    <div class="flex flex-col sm:flex-row gap-3 sm:items-center justify-between">
        <form method="GET" class="flex flex-wrap gap-2">
            <input name="q" value="{{ request('q') }}" placeholder="Cari nama / NIS..." class="rounded-xl border border-slate-200 px-3 py-2 text-sm w-44">
            <select name="kelas_id" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                <option value="">Semua Kelas</option>
                @foreach ($kelasList as $k)<option value="{{ $k->id }}" @selected(request('kelas_id') == $k->id)>{{ $k->name }}</option>@endforeach
            </select>
            <select name="status" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                <option value="">Semua Status</option>
                @foreach (['Aktif','Lulus','Pindah','Keluar','Nonaktif'] as $st)<option value="{{ $st }}" @selected(request('status') === $st)>{{ $st }}</option>@endforeach
            </select>
            <button class="rounded-xl bg-slate-100 px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-200">Filter</button>
        </form>
        @if ($canManage)
            <button @click="openCreate()" class="rounded-xl bg-brand-navy text-white px-4 py-2.5 text-sm font-bold hover:bg-brand-navy-dark flex items-center gap-2 shrink-0"><i class="ri-user-add-line"></i> Tambah Santri</button>
        @endif
    </div>

    {{-- Bar aksi massal --}}
    <div x-show="selected.length" x-cloak class="sticky top-0 z-20 flex flex-wrap items-center gap-3 bg-brand-navy text-white rounded-2xl px-4 py-3 shadow-lg">
        <span class="text-sm font-bold"><span x-text="selected.length"></span> santri dipilih</span>
        <div class="flex flex-wrap gap-2 ml-auto">
            <form method="POST" action="{{ route('app.santri.bulk-cards') }}" target="_blank">
                @csrf
                <template x-for="id in selected" :key="'c'+id"><input type="hidden" name="ids[]" :value="id"></template>
                <button class="rounded-lg bg-white/15 hover:bg-white/25 px-3 py-1.5 text-xs font-bold flex items-center gap-1.5"><i class="ri-id-card-line"></i> Download Kartu</button>
            </form>
            <form method="POST" action="{{ route('app.santri.export') }}">
                @csrf
                <template x-for="id in selected" :key="'e'+id"><input type="hidden" name="ids[]" :value="id"></template>
                <button class="rounded-lg bg-white/15 hover:bg-white/25 px-3 py-1.5 text-xs font-bold flex items-center gap-1.5"><i class="ri-file-excel-2-line"></i> Download Excel</button>
            </form>
            @if ($canManage)
                <form method="POST" action="{{ route('app.santri.bulk-move-class') }}" class="flex items-center gap-1" data-confirm-title="Pindah Kelas Massal" data-confirm-label="Ya, Pindahkan" :data-confirm="'Pindahkan ' + selected.length + ' santri terpilih ke kelas tujuan?'">
                    @csrf
                    <template x-for="id in selected" :key="'m'+id"><input type="hidden" name="ids[]" :value="id"></template>
                    <select name="kelas_id" required class="rounded-lg bg-white/15 text-white px-2 py-1.5 text-xs font-semibold border-0 focus:ring-2 focus:ring-white/40 [&>option]:text-slate-700">
                        <option value="" class="text-slate-700">Pindah ke…</option>
                        @foreach ($kelasList as $k)<option value="{{ $k->id }}">{{ $k->name }}</option>@endforeach
                    </select>
                    <button class="rounded-lg bg-white/15 hover:bg-white/25 px-3 py-1.5 text-xs font-bold flex items-center gap-1.5"><i class="ri-arrow-left-right-line"></i> Pindah</button>
                </form>
                <form method="POST" action="{{ route('app.santri.bulk-regenerate') }}" data-confirm-title="Token Baru Massal" data-confirm-label="Ya, Buat Ulang" :data-confirm="'Buat ulang token kartu untuk ' + selected.length + ' santri terpilih? Kartu lama tidak akan valid lagi.'">
                    @csrf
                    <template x-for="id in selected" :key="'t'+id"><input type="hidden" name="ids[]" :value="id"></template>
                    <button class="rounded-lg bg-amber-400 text-brand-navy hover:bg-amber-300 px-3 py-1.5 text-xs font-bold flex items-center gap-1.5"><i class="ri-refresh-line"></i> Token Baru</button>
                </form>
            @endif
            <button type="button" @click="selected=[]" class="rounded-lg bg-white/10 hover:bg-white/20 px-3 py-1.5 text-xs font-semibold">Batal</button>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-slate-500 text-xs uppercase">
                    <tr><th class="px-5 py-3 w-10"><input type="checkbox" @change="toggleAll($event)" :checked="allChecked" class="rounded border-slate-300 text-brand-navy"></th><th class="text-left px-5 py-3">Santri</th><th class="text-left px-5 py-3">Kelas</th><th class="text-left px-5 py-3">Wali</th><th class="text-left px-5 py-3">Status</th><th class="text-right px-5 py-3">Aksi</th></tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($santri as $s)
                        <tr :class="selected.includes({{ $s->id }}) ? 'bg-brand-sky/30' : ''">
                            <td class="px-5 py-3"><input type="checkbox" value="{{ $s->id }}" x-model.number="selected" class="rounded border-slate-300 text-brand-navy"></td>
                            <td class="px-5 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="h-9 w-9 rounded-full bg-brand-sky text-brand-navy flex items-center justify-center text-xs font-bold overflow-hidden">
                                        @if ($s->photoUrl())<img src="{{ $s->photoUrl() }}" class="h-full w-full object-cover">@else {{ strtoupper(mb_substr($s->name, 0, 1)) }} @endif
                                    </div>
                                    <div><p class="font-semibold text-brand-navy">{{ $s->name }}</p><p class="text-[11px] text-slate-400">NIS {{ $s->nis }}</p></div>
                                </div>
                            </td>
                            <td class="px-5 py-3 text-slate-600">{{ $s->kelas?->name ?? '-' }}</td>
                            <td class="px-5 py-3 text-slate-600">{{ $s->wali_name ?? '-' }}</td>
                            <td class="px-5 py-3"><span class="text-[10px] font-bold px-2 py-0.5 rounded-full {{ $statusBadge($s->status) }}">{{ $s->status }}</span></td>
                            <td class="px-5 py-3">
                                <div class="flex justify-end gap-1">
                                    <a href="{{ route('app.santri.history', $s) }}" class="h-8 w-8 rounded-lg bg-slate-100 text-slate-500 hover:bg-brand-sky hover:text-brand-navy flex items-center justify-center" title="Riwayat Kelas"><i class="ri-history-line"></i></a>
                                    <button type="button" @click='openCard(@json($s))' class="h-8 w-8 rounded-lg bg-brand-sky text-brand-navy hover:bg-brand-navy hover:text-white flex items-center justify-center" title="Kartu Santri"><i class="ri-qr-code-line"></i></button>
                                    @if ($canManage)
                                        <button @click='openEdit(@json($s))' class="h-8 w-8 rounded-lg bg-slate-100 text-slate-600 hover:bg-brand-sky flex items-center justify-center"><i class="ri-pencil-line"></i></button>
                                        <form method="POST" action="{{ route('app.santri.destroy', $s) }}" data-confirm="Hapus data {{ $s->name }}? Tindakan ini tidak dapat dibatalkan." data-confirm-title="Hapus Santri" data-confirm-danger>@csrf @method('DELETE')<button class="h-8 w-8 rounded-lg bg-red-50 text-red-500 hover:bg-red-100 flex items-center justify-center"><i class="ri-delete-bin-line"></i></button></form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-5 py-10 text-center text-slate-400">Belum ada data santri.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-5 py-3">{{ $santri->links() }}</div>
    </div>

    {{-- Modal Kartu Identitas Santri (kartu digital gradien) --}}
    <div x-show="cardOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div @click="cardOpen=false" class="absolute inset-0"></div>
        <div class="relative bg-white rounded-3xl shadow-2xl w-full max-w-sm border border-slate-100 overflow-hidden flex flex-col">
            <div class="px-6 py-4 bg-brand-navy text-white flex justify-between items-center">
                <span class="text-xs font-bold uppercase tracking-wider">Kartu Identitas Santri</span>
                <button @click="cardOpen=false" class="text-white hover:text-brand-sky text-xl"><i class="ri-close-line"></i></button>
            </div>
            <div class="p-6 flex justify-center bg-slate-50">
                <div class="w-80 bg-gradient-to-b from-brand-navy via-[#0d276b] to-brand-navy-dark rounded-3xl p-5 text-white flex flex-col gap-4 shadow-xl relative overflow-hidden border border-white/10">
                    <div class="absolute -top-20 -right-20 h-40 w-40 rounded-full bg-brand-green/25 blur-xl"></div>
                    <div class="absolute -bottom-20 -left-20 h-40 w-40 rounded-full bg-brand-teal/20 blur-xl"></div>
                    <div class="flex items-center gap-2.5 pb-3 border-b border-white/10 z-10">
                        @if ($branding['logo_type'] === 'image' && \App\Support\Branding::logoImageUrl())
                            <div class="h-8 w-8 rounded-lg overflow-hidden bg-white flex items-center justify-center shrink-0"><img src="{{ \App\Support\Branding::logoImageUrl() }}" alt="Logo" class="h-full w-full object-cover"></div>
                        @else
                            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-green text-white font-bold text-sm shrink-0">{{ $branding['logo_text'] }}</div>
                        @endif
                        <div><span class="text-xs font-bold block leading-none">{{ $branding['pondok_name'] }}</span><span class="text-[8px] uppercase tracking-wider text-brand-green block mt-0.5">Kartu Digital Santri</span></div>
                    </div>
                    <div class="flex flex-col items-center space-y-3 z-10 pt-2">
                        <div class="h-28 w-24 rounded-2xl bg-slate-800 border-2 border-brand-green/50 flex items-center justify-center text-white/50 text-4xl overflow-hidden shadow-inner">
                            <template x-if="cardTarget.photo"><img :src="cardTarget.photo" class="h-full w-full object-cover"></template>
                            <template x-if="!cardTarget.photo"><i class="ri-user-3-fill"></i></template>
                        </div>
                        <div class="text-center">
                            <h4 class="text-sm font-extrabold tracking-tight" x-text="cardTarget.name"></h4>
                            <span class="text-[10px] text-brand-sky/80 uppercase font-semibold block mt-0.5" x-text="'Kelas ' + cardTarget.kelas"></span>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-2 text-center text-[9px] font-bold text-brand-sky/60 z-10 bg-white/5 p-2.5 border border-white/5 rounded-xl">
                        <div><span class="block">NOMOR INDUK (NIS)</span><span class="text-white text-xs" x-text="cardTarget.nis"></span></div>
                        <div><span class="block">STATUS SANTRI</span><span class="text-brand-green text-xs" x-text="cardTarget.status"></span></div>
                    </div>
                    <div class="flex flex-col items-center space-y-1.5 z-10 border-t border-white/10 pt-3">
                        <div id="cardQr" class="p-2 bg-white rounded-xl shadow-md flex items-center justify-center"></div>
                        <span class="text-[8px] font-bold text-brand-sky/60 uppercase tracking-widest" x-text="'TOKEN: ' + cardTarget.token"></span>
                    </div>
                </div>
            </div>
            <div class="px-6 py-4 bg-slate-50 border-t flex justify-end gap-3">
                <button @click="cardOpen=false" class="border border-slate-200 bg-white hover:bg-slate-50 rounded-xl px-4 py-2 text-xs font-semibold text-slate-600">Tutup</button>
                <a :href="cardTarget.printUrl" class="bg-brand-green text-white hover:bg-brand-green-dark rounded-xl px-4 py-2 text-xs font-bold shadow-md flex items-center gap-1"><i class="ri-printer-line"></i> Cetak Kartu Fisik</a>
            </div>
        </div>
    </div>

    @if ($canManage)
        <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div @click="open=false" class="absolute inset-0 bg-black/50"></div>
            <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-2xl p-6 max-h-[90vh] overflow-y-auto">
                <h3 class="font-bold text-brand-navy mb-4" x-text="mode==='create' ? 'Tambah Santri' : 'Edit Santri'"></h3>
                <form :action="actionUrl" method="POST" enctype="multipart/form-data" class="grid sm:grid-cols-2 gap-3">
                    @csrf
                    <template x-if="mode==='edit'"><input type="hidden" name="_method" value="PUT"></template>
                    <div><label class="text-xs font-bold text-slate-500 uppercase">Nama</label><input name="name" x-model="form.name" required class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm"></div>
                    <div><label class="text-xs font-bold text-slate-500 uppercase">NIS</label><input name="nis" x-model="form.nis" required class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm"></div>
                    <div><label class="text-xs font-bold text-slate-500 uppercase">NISN</label><input name="nisn" x-model="form.nisn" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm"></div>
                    <div><label class="text-xs font-bold text-slate-500 uppercase">Jenis Kelamin</label><select name="gender" x-model="form.gender" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm"><option value="">-</option><option value="L">Laki-laki</option><option value="P">Perempuan</option></select></div>
                    <div><label class="text-xs font-bold text-slate-500 uppercase">Kelas</label><select name="kelas_id" x-model="form.kelas_id" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm"><option value="">-</option>@foreach ($kelasList as $k)<option value="{{ $k->id }}">{{ $k->name }}</option>@endforeach</select></div>
                    <div><label class="text-xs font-bold text-slate-500 uppercase">Status</label><select name="status" x-model="form.status" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">@foreach (['Aktif','Lulus','Pindah','Keluar','Nonaktif'] as $st)<option value="{{ $st }}">{{ $st }}</option>@endforeach</select></div>
                    <div><label class="text-xs font-bold text-slate-500 uppercase">Tempat Lahir</label><input name="birth_place" x-model="form.birth_place" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm"></div>
                    <div><label class="text-xs font-bold text-slate-500 uppercase">Tanggal Lahir</label><input type="date" name="birth_date" x-model="form.birth_date" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm"></div>
                    <div><label class="text-xs font-bold text-slate-500 uppercase">Nama Wali</label><input name="wali_name" x-model="form.wali_name" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm"></div>
                    <div><label class="text-xs font-bold text-slate-500 uppercase">HP Wali</label><input name="wali_phone" x-model="form.wali_phone" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm"></div>
                    <div class="sm:col-span-2"><label class="text-xs font-bold text-slate-500 uppercase">Alamat</label><input name="address" x-model="form.address" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm"></div>
                    <div class="sm:col-span-2"><label class="text-xs font-bold text-slate-500 uppercase">Foto (opsional)</label><input type="file" name="photo" accept="image/*" class="mt-1 w-full text-xs text-slate-500 file:mr-3 file:rounded-lg file:border-0 file:bg-slate-100 file:px-3 file:py-2 file:text-xs file:font-semibold"></div>
                    <div class="sm:col-span-2 flex justify-end gap-2 pt-2">
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
<script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
<script>
    function santriModule() {
        return {
            open: false, mode: 'create',
            cardOpen: false,
            cardTarget: { name: '', kelas: '-', nis: '', status: '', token: '', photo: null, printUrl: '#' },
            selected: [],
            pageIds: @json($santri->pluck('id')),
            get allChecked() { return this.pageIds.length > 0 && this.pageIds.every(id => this.selected.includes(id)); },
            toggleAll(e) { this.selected = e.target.checked ? [...this.pageIds] : []; },
            form: {},
            blank: { id: null, name: '', nis: '', nisn: '', gender: '', kelas_id: '', status: 'Aktif', birth_place: '', birth_date: '', wali_name: '', wali_phone: '', address: '' },
            get actionUrl() { return this.mode === 'create' ? '{{ route('app.santri.store') }}' : '{{ url('app/santri') }}/' + this.form.id; },
            openCreate() { this.mode = 'create'; this.form = { ...this.blank }; this.open = true; },
            openEdit(s) {
                this.mode = 'edit';
                this.form = { id: s.id, name: s.name, nis: s.nis, nisn: s.nisn ?? '', gender: s.gender ?? '', kelas_id: s.kelas_id ?? '', status: s.status, birth_place: s.birth_place ?? '', birth_date: s.birth_date ? s.birth_date.substring(0,10) : '', wali_name: s.wali_name ?? '', wali_phone: s.wali_phone ?? '', address: s.address ?? '' };
                this.open = true;
            },
            openCard(s) {
                this.cardTarget = {
                    name: s.name, kelas: s.kelas?.name ?? '-', nis: s.nis, status: s.status,
                    token: s.card_token, photo: s.photo_path ? '/storage/' + s.photo_path : null,
                    printUrl: '{{ url('app/santri') }}/' + s.id + '/kartu',
                };
                this.cardOpen = true;
                this.$nextTick(() => {
                    const el = document.getElementById('cardQr');
                    if (el) { el.innerHTML = ''; if (typeof QRCode !== 'undefined') new QRCode(el, { text: s.card_token, width: 80, height: 80, correctLevel: QRCode.CorrectLevel.M }); }
                });
            },
        };
    }
</script>
@endpush
