@extends('layouts.app')
@section('title', 'Profil Saya')

@section('content')
<div class="max-w-3xl space-y-6">
    {{-- Identitas --}}
    <div class="bg-white rounded-2xl border border-slate-200 p-6">
        <div class="flex items-center gap-4 mb-5">
            <div class="h-14 w-14 rounded-2xl bg-brand-navy text-white flex items-center justify-center text-lg font-bold">{{ $user->initials() }}</div>
            <div>
                <h2 class="font-bold text-brand-navy">{{ $user->name }}</h2>
                <p class="text-xs text-slate-400">{{ $user->role?->label }} @if($user->personil) · {{ $user->personil->jabatan }} @endif</p>
            </div>
        </div>

        <form method="POST" action="{{ route('app.profile.update') }}" class="grid sm:grid-cols-2 gap-4">
            @csrf @method('PUT')
            <div>
                <label class="text-xs font-bold text-slate-500 uppercase">Nama</label>
                <input name="name" value="{{ old('name', $user->name) }}" required class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm focus:border-brand-navy focus:ring-1 focus:ring-brand-navy">
            </div>
            <div>
                <label class="text-xs font-bold text-slate-500 uppercase">Email</label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}" required class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm focus:border-brand-navy focus:ring-1 focus:ring-brand-navy">
            </div>
            <div>
                <label class="text-xs font-bold text-slate-500 uppercase">No. HP</label>
                <input name="phone" value="{{ old('phone', $user->personil?->phone) }}" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm focus:border-brand-navy focus:ring-1 focus:ring-brand-navy">
            </div>
            <div>
                <label class="text-xs font-bold text-slate-500 uppercase">Alamat</label>
                <input name="address" value="{{ old('address', $user->personil?->address) }}" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm focus:border-brand-navy focus:ring-1 focus:ring-brand-navy">
            </div>
            <div class="sm:col-span-2">
                <button class="rounded-xl bg-brand-navy text-white px-5 py-2.5 text-sm font-bold hover:bg-brand-navy-dark">Simpan Profil</button>
            </div>
        </form>
    </div>

    {{-- Ganti password --}}
    <div class="bg-white rounded-2xl border border-slate-200 p-6">
        <h3 class="font-bold text-brand-navy mb-4 flex items-center gap-2"><i class="ri-lock-password-line text-brand-green"></i> Ganti Kata Sandi</h3>
        <form method="POST" action="{{ route('app.profile.password') }}" class="grid sm:grid-cols-3 gap-4">
            @csrf @method('PUT')
            <div>
                <label class="text-xs font-bold text-slate-500 uppercase">Sandi Saat Ini</label>
                <input type="password" name="current_password" required class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm focus:border-brand-navy focus:ring-1 focus:ring-brand-navy">
            </div>
            <div>
                <label class="text-xs font-bold text-slate-500 uppercase">Sandi Baru</label>
                <input type="password" name="password" required class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm focus:border-brand-navy focus:ring-1 focus:ring-brand-navy">
            </div>
            <div>
                <label class="text-xs font-bold text-slate-500 uppercase">Ulangi Sandi</label>
                <input type="password" name="password_confirmation" required class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm focus:border-brand-navy focus:ring-1 focus:ring-brand-navy">
            </div>
            <div class="sm:col-span-3">
                <button class="rounded-xl bg-brand-green text-white px-5 py-2.5 text-sm font-bold hover:bg-brand-green-dark">Perbarui Sandi</button>
            </div>
        </form>
    </div>
</div>
@endsection
