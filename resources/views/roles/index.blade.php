@extends('layouts.app')
@section('title', 'Role & Hak Akses')

@section('content')
<div x-data="{ createOpen: false }" class="space-y-5">
    <div class="flex justify-end">
        <button @click="createOpen=true" class="rounded-xl bg-brand-navy text-white px-4 py-2.5 text-sm font-bold hover:bg-brand-navy-dark flex items-center gap-2"><i class="ri-add-line"></i> Tambah Role</button>
    </div>

    <div class="space-y-4">
        @foreach ($roles as $role)
            <div x-data="{ open: false }" class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
                <div class="px-5 py-4 flex items-center justify-between cursor-pointer" @click="open=!open">
                    <div class="flex items-center gap-3">
                        <span class="h-10 w-10 rounded-xl bg-brand-navy/10 text-brand-navy flex items-center justify-center"><i class="ri-shield-user-line"></i></span>
                        <div>
                            <p class="font-bold text-brand-navy">{{ $role->label }} @if ($role->is_system)<span class="ml-1 text-[9px] font-bold px-2 py-0.5 rounded-full bg-slate-100 text-slate-500">SISTEM</span>@endif</p>
                            <p class="text-[11px] text-slate-400">{{ $role->users_count }} user · {{ $role->name === 'superadmin' ? 'Akses penuh' : $role->permissions->count() . ' permission' }}</p>
                        </div>
                    </div>
                    <i class="ri-arrow-down-s-line text-slate-400 transition-transform" :class="open && 'rotate-180'"></i>
                </div>

                <div x-show="open" x-cloak class="border-t border-slate-100 p-5">
                    @if ($role->name === 'superadmin')
                        <p class="text-sm text-slate-500"><i class="ri-information-line"></i> Super Admin secara otomatis memiliki seluruh hak akses dan tidak dapat diubah.</p>
                    @else
                        <form method="POST" action="{{ route('app.roles.permissions', $role) }}">
                            @csrf @method('PUT')
                            <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-5">
                                @foreach ($permissions as $group => $perms)
                                    <div>
                                        <p class="text-[11px] font-bold uppercase tracking-wide text-slate-400 mb-2">{{ $group }}</p>
                                        <div class="space-y-1.5">
                                            @foreach ($perms as $perm)
                                                <label class="flex items-start gap-2 text-xs text-slate-600">
                                                    <input type="checkbox" name="permissions[]" value="{{ $perm->id }}" @checked($role->permissions->contains('id', $perm->id)) class="mt-0.5 rounded border-slate-300 text-brand-navy">
                                                    <span>{{ $perm->label }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <div class="flex items-center justify-between mt-5 pt-4 border-t border-slate-100">
                                @unless ($role->is_system)
                                    <button type="button" onclick="document.getElementById('del-role-{{ $role->id }}').requestSubmit()" class="text-xs font-bold text-red-500 hover:underline">Hapus Role</button>
                                @else
                                    <span></span>
                                @endunless
                                <button class="rounded-xl bg-brand-green text-white px-5 py-2 text-sm font-bold hover:bg-brand-green-dark">Simpan Hak Akses</button>
                            </div>
                        </form>
                        @unless ($role->is_system)
                            <form id="del-role-{{ $role->id }}" method="POST" action="{{ route('app.roles.destroy', $role) }}" data-confirm="Hapus role ini? Pastikan tidak ada pengguna yang masih memakai role ini." data-confirm-title="Hapus Role" data-confirm-danger class="hidden">@csrf @method('DELETE')</form>
                        @endunless
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    {{-- Modal tambah role --}}
    <div x-show="createOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div @click="createOpen=false" class="absolute inset-0 bg-black/50"></div>
        <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-sm p-6">
            <h3 class="font-bold text-brand-navy mb-4">Tambah Role</h3>
            <form method="POST" action="{{ route('app.roles.store') }}" class="space-y-3">
                @csrf
                <div><label class="text-xs font-bold text-slate-500 uppercase">Nama Role</label><input name="label" required class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm"></div>
                <div><label class="text-xs font-bold text-slate-500 uppercase">Deskripsi</label><input name="description" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm"></div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="createOpen=false" class="rounded-xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600">Batal</button>
                    <button class="rounded-xl bg-brand-navy text-white px-5 py-2 text-sm font-bold hover:bg-brand-navy-dark">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
