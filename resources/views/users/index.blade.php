@extends('layouts.app')
@section('title', 'Manajemen User')

@section('content')
<div x-data="userModule()" class="space-y-5">
    <div class="flex justify-end">
        <button @click="openCreate()" class="rounded-xl bg-brand-navy text-white px-4 py-2.5 text-sm font-bold hover:bg-brand-navy-dark flex items-center gap-2"><i class="ri-user-add-line"></i> Tambah User</button>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-slate-500 text-xs uppercase">
                    <tr><th class="text-left px-5 py-3">Nama</th><th class="text-left px-5 py-3">Email</th><th class="text-left px-5 py-3">Role</th><th class="text-left px-5 py-3">Status</th><th class="text-right px-5 py-3">Aksi</th></tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($users as $u)
                        <tr>
                            <td class="px-5 py-3 font-semibold text-brand-navy">{{ $u->name }}</td>
                            <td class="px-5 py-3 text-slate-600">{{ $u->email }}</td>
                            <td class="px-5 py-3"><span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-brand-sky text-brand-navy">{{ $u->role?->label ?? '-' }}</span></td>
                            <td class="px-5 py-3">@if ($u->is_active)<span class="text-emerald-600 text-xs font-semibold">Aktif</span>@else<span class="text-red-500 text-xs font-semibold">Nonaktif</span>@endif</td>
                            <td class="px-5 py-3">
                                <div class="flex justify-end gap-1">
                                    <button @click='openEdit(@json($u))' class="h-8 w-8 rounded-lg bg-slate-100 text-slate-600 hover:bg-brand-sky flex items-center justify-center"><i class="ri-pencil-line"></i></button>
                                    <button @click='openReset(@json($u))' class="h-8 w-8 rounded-lg bg-amber-50 text-amber-600 hover:bg-amber-100 flex items-center justify-center" title="Reset password"><i class="ri-key-2-line"></i></button>
                                    @if ($u->id !== auth()->id())
                                        <form method="POST" action="{{ route('app.users.destroy', $u) }}" data-confirm="Hapus akun pengguna ini?" data-confirm-title="Hapus Akun" data-confirm-danger>@csrf @method('DELETE')<button class="h-8 w-8 rounded-lg bg-red-50 text-red-500 hover:bg-red-100 flex items-center justify-center"><i class="ri-delete-bin-line"></i></button></form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-5 py-3">{{ $users->links() }}</div>
    </div>

    {{-- Modal create/edit --}}
    <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div @click="open=false" class="absolute inset-0 bg-black/50"></div>
        <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-md p-6">
            <h3 class="font-bold text-brand-navy mb-4" x-text="mode==='create' ? 'Tambah User' : 'Edit User'"></h3>
            <form :action="actionUrl" method="POST" class="space-y-3">
                @csrf
                <template x-if="mode==='edit'"><input type="hidden" name="_method" value="PUT"></template>
                <div><label class="text-xs font-bold text-slate-500 uppercase">Nama</label><input name="name" x-model="form.name" required class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm"></div>
                <div><label class="text-xs font-bold text-slate-500 uppercase">Email</label><input type="email" name="email" x-model="form.email" required class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm"></div>
                <template x-if="mode==='create'">
                    <div class="grid grid-cols-2 gap-2">
                        <div><label class="text-xs font-bold text-slate-500 uppercase">Password</label><input type="password" name="password" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm"></div>
                        <div><label class="text-xs font-bold text-slate-500 uppercase">Ulangi</label><input type="password" name="password_confirmation" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm"></div>
                    </div>
                </template>
                <div><label class="text-xs font-bold text-slate-500 uppercase">Role</label>
                    <select name="role_id" x-model="form.role_id" required class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
                        @foreach ($roles as $r)<option value="{{ $r->id }}">{{ $r->label }}</option>@endforeach
                    </select>
                </div>
                <div><label class="text-xs font-bold text-slate-500 uppercase">Tautkan Personil</label>
                    <select name="personil_id" x-model="form.personil_id" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
                        <option value="">— Tanpa personil —</option>
                        <template x-if="form.linked_personil_id">
                            <option :value="form.linked_personil_id" x-text="form.linked_personil_label" selected></option>
                        </template>
                        @foreach ($availablePersonil as $p)<option value="{{ $p->id }}">{{ $p->name }}</option>@endforeach
                    </select>
                </div>
                <label class="flex items-center gap-2 text-sm text-slate-600"><input type="checkbox" name="is_active" value="1" x-model="form.is_active" class="rounded border-slate-300 text-brand-navy"> Akun aktif</label>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="open=false" class="rounded-xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600">Batal</button>
                    <button class="rounded-xl bg-brand-navy text-white px-5 py-2 text-sm font-bold hover:bg-brand-navy-dark">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal reset password --}}
    <div x-show="resetOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div @click="resetOpen=false" class="absolute inset-0 bg-black/50"></div>
        <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-sm p-6">
            <h3 class="font-bold text-brand-navy mb-1">Reset Password</h3>
            <p class="text-xs text-slate-400 mb-4" x-text="'Akun: ' + resetName"></p>
            <form :action="resetUrl" method="POST" class="space-y-3">
                @csrf
                <input type="password" name="password" placeholder="Password baru" required class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
                <input type="password" name="password_confirmation" placeholder="Ulangi password" required class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
                <div class="flex justify-end gap-2">
                    <button type="button" @click="resetOpen=false" class="rounded-xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600">Batal</button>
                    <button class="rounded-xl bg-amber-500 text-white px-5 py-2 text-sm font-bold hover:bg-amber-600">Reset</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function userModule() {
        return {
            open: false, resetOpen: false, mode: 'create', resetName: '', resetId: null,
            form: { id: null, name: '', email: '', role_id: '', personil_id: '', is_active: true, linked_personil_id: '', linked_personil_label: '' },
            get actionUrl() { return this.mode === 'create' ? '{{ route('app.users.index') }}' : '{{ url('app/users') }}/' + this.form.id; },
            get resetUrl() { return '{{ url('app/users') }}/' + this.resetId + '/reset-password'; },
            openCreate() { this.mode = 'create'; this.form = { id: null, name: '', email: '', role_id: '{{ $roles->first()?->id }}', personil_id: '', is_active: true, linked_personil_id: '', linked_personil_label: '' }; this.open = true; },
            openEdit(u) {
                this.mode = 'edit';
                this.form = { id: u.id, name: u.name, email: u.email, role_id: u.role_id ?? '', personil_id: u.personil ? u.personil.id : '', is_active: !!u.is_active, linked_personil_id: u.personil ? u.personil.id : '', linked_personil_label: u.personil ? u.personil.name : '' };
                this.open = true;
            },
            openReset(u) { this.resetId = u.id; this.resetName = u.name; this.resetOpen = true; },
        };
    }
</script>
@endpush
