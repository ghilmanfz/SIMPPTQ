@extends('layouts.app')
@section('title', 'Data Personil')

@php
    $fungsiBadge = fn ($f) => match ($f) {
        'Pengajar' => 'bg-emerald-100 text-emerald-700',
        'Dua Fungsi' => 'bg-violet-100 text-violet-700',
        default => 'bg-slate-100 text-slate-600',
    };
@endphp

@section('content')
<div x-data="personilModule()" class="space-y-5">
    <div class="flex flex-col sm:flex-row gap-3 sm:items-center justify-between">
        <form method="GET" class="flex flex-wrap gap-2">
            <input name="q" value="{{ request('q') }}" placeholder="Cari nama / NIK..." class="rounded-xl border border-slate-200 px-3 py-2 text-sm w-44">
            <select name="fungsi" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                <option value="">Semua Fungsi</option>
                @foreach (['Non-Pengajar','Pengajar','Dua Fungsi'] as $f)<option value="{{ $f }}" @selected(request('fungsi') === $f)>{{ $f }}</option>@endforeach
            </select>
            <button class="rounded-xl bg-slate-100 px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-200">Filter</button>
        </form>
        <div class="flex gap-2 shrink-0">
            <a href="{{ route('app.personil.export', request()->only('q', 'fungsi', 'status')) }}" class="rounded-xl bg-brand-green text-white px-4 py-2.5 text-sm font-bold hover:bg-brand-green-dark flex items-center gap-2"><i class="ri-file-excel-2-line"></i> Excel</a>
            @if ($canManage)
                <button @click="openCreate()" class="rounded-xl bg-brand-navy text-white px-4 py-2.5 text-sm font-bold hover:bg-brand-navy-dark flex items-center gap-2"><i class="ri-user-add-line"></i> Tambah Personil</button>
            @endif
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-slate-500 text-xs uppercase">
                    <tr><th class="text-left px-5 py-3">Nama</th><th class="text-left px-5 py-3">Jabatan</th><th class="text-left px-5 py-3">Fungsi</th><th class="text-left px-5 py-3">Status</th><th class="text-left px-5 py-3">Akun</th><th class="text-right px-5 py-3">Aksi</th></tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($personil as $p)
                        <tr>
                            <td class="px-5 py-3"><p class="font-semibold text-brand-navy">{{ $p->name }}</p><p class="text-[11px] text-slate-400">{{ $p->nik ?? '—' }}</p></td>
                            <td class="px-5 py-3 text-slate-600">{{ $p->jabatan ?? '-' }}</td>
                            <td class="px-5 py-3"><span class="text-[10px] font-bold px-2 py-0.5 rounded-full {{ $fungsiBadge($p->fungsi_kerja) }}">{{ $p->fungsi_kerja }}</span></td>
                            <td class="px-5 py-3 text-slate-600">{{ $p->status_kerja }}</td>
                            <td class="px-5 py-3">@if ($p->user)<span class="text-emerald-600 text-xs"><i class="ri-checkbox-circle-line"></i> {{ $p->user->role?->label }}</span>@else<span class="text-slate-400 text-xs">—</span>@endif</td>
                            <td class="px-5 py-3">
                                <div class="flex justify-end gap-1">
                                    <a href="{{ route('app.personil.show', $p) }}" class="h-8 w-8 rounded-lg bg-brand-sky text-brand-navy hover:bg-brand-navy hover:text-white flex items-center justify-center"><i class="ri-eye-line"></i></a>
                                    @if ($canManage)
                                        <button @click='openEdit(@json($p))' class="h-8 w-8 rounded-lg bg-slate-100 text-slate-600 hover:bg-brand-sky flex items-center justify-center"><i class="ri-pencil-line"></i></button>
                                        <form method="POST" action="{{ route('app.personil.destroy', $p) }}" data-confirm="Hapus data personil ini? Tindakan ini tidak dapat dibatalkan." data-confirm-title="Hapus Personil" data-confirm-danger>@csrf @method('DELETE')<button class="h-8 w-8 rounded-lg bg-red-50 text-red-500 hover:bg-red-100 flex items-center justify-center"><i class="ri-delete-bin-line"></i></button></form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-5 py-10 text-center text-slate-400">Belum ada data personil.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-5 py-3">{{ $personil->links() }}</div>
    </div>

    @if ($canManage)
        <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div @click="open=false" class="absolute inset-0 bg-black/50"></div>
            <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-2xl p-6 max-h-[90vh] overflow-y-auto">
                <h3 class="font-bold text-brand-navy mb-4" x-text="mode==='create' ? 'Tambah Personil' : 'Edit Personil'"></h3>
                <form :action="actionUrl" method="POST" enctype="multipart/form-data" class="grid sm:grid-cols-2 gap-3">
                    @csrf
                    <template x-if="mode==='edit'"><input type="hidden" name="_method" value="PUT"></template>
                    <div><label class="text-xs font-bold text-slate-500 uppercase">Nama</label><input name="name" x-model="form.name" required class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm"></div>
                    <div><label class="text-xs font-bold text-slate-500 uppercase">NIK</label><input name="nik" x-model="form.nik" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm"></div>
                    <div><label class="text-xs font-bold text-slate-500 uppercase">Jabatan</label><input name="jabatan" x-model="form.jabatan" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm"></div>
                    <div><label class="text-xs font-bold text-slate-500 uppercase">Unit Kerja</label><input name="unit_kerja" x-model="form.unit_kerja" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm"></div>
                    <div><label class="text-xs font-bold text-slate-500 uppercase">Fungsi Kerja</label><select name="fungsi_kerja" x-model="form.fungsi_kerja" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm"><option>Non-Pengajar</option><option>Pengajar</option><option>Dua Fungsi</option></select></div>
                    <div><label class="text-xs font-bold text-slate-500 uppercase">Status Kerja</label><select name="status_kerja" x-model="form.status_kerja" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm"><option>Tetap</option><option>Tidak Tetap</option><option>GTY</option><option>GTT</option><option>Kontrak</option></select></div>
                    <div><label class="text-xs font-bold text-slate-500 uppercase">No. HP</label><input name="phone" x-model="form.phone" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm"></div>
                    <div><label class="text-xs font-bold text-slate-500 uppercase">Email</label><input name="email" x-model="form.email" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm"></div>
                    <div><label class="text-xs font-bold text-slate-500 uppercase">Gaji Pokok</label><input type="number" name="salary_base" x-model="form.salary_base" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm"></div>
                    <div><label class="text-xs font-bold text-slate-500 uppercase">Tunjangan</label><input type="number" name="salary_allowance" x-model="form.salary_allowance" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm"></div>
                    <div><label class="text-xs font-bold text-slate-500 uppercase">Potongan</label><input type="number" name="salary_deduction" x-model="form.salary_deduction" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm"></div>
                    <div><label class="text-xs font-bold text-slate-500 uppercase">Tautkan Akun</label>
                        <select name="user_id" x-model="form.user_id" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
                            <option value="">— Tanpa akun —</option>
                            <template x-if="form.linked_user_id">
                                <option :value="form.linked_user_id" x-text="form.linked_user_label" selected></option>
                            </template>
                            @foreach ($availableUsers as $u)<option value="{{ $u->id }}">{{ $u->name }} ({{ $u->email }})</option>@endforeach
                        </select>
                    </div>
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
<script>
    function personilModule() {
        return {
            open: false, mode: 'create', form: {},
            blank: { id: null, name: '', nik: '', jabatan: '', unit_kerja: '', fungsi_kerja: 'Non-Pengajar', status_kerja: 'Tetap', phone: '', email: '', salary_base: 0, salary_allowance: 0, salary_deduction: 0, user_id: '', linked_user_id: '', linked_user_label: '' },
            get actionUrl() { return this.mode === 'create' ? '{{ route('app.personil.store') }}' : '{{ url('app/personil') }}/' + this.form.id; },
            openCreate() { this.mode = 'create'; this.form = { ...this.blank }; this.open = true; },
            openEdit(p) {
                this.mode = 'edit';
                this.form = { id: p.id, name: p.name, nik: p.nik ?? '', jabatan: p.jabatan ?? '', unit_kerja: p.unit_kerja ?? '', fungsi_kerja: p.fungsi_kerja, status_kerja: p.status_kerja, phone: p.phone ?? '', email: p.email ?? '', salary_base: p.salary_base, salary_allowance: p.salary_allowance, salary_deduction: p.salary_deduction, user_id: p.user_id ?? '', linked_user_id: p.user_id ?? '', linked_user_label: p.user ? (p.user.name + ' (' + p.user.email + ')') : '' };
                this.open = true;
            },
        };
    }
</script>
@endpush
