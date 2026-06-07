@extends('layouts.app')
@section('title', 'Integrasi WhatsApp Fonnte')

@php($connected = ($branding['whatsapp_connected'] ?? '0') === '1')

@section('content')
<div class="max-w-3xl space-y-6">
    {{-- Status --}}
    <div class="bg-white rounded-2xl border border-slate-200 p-5 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <span class="h-12 w-12 rounded-xl bg-green-100 text-green-600 flex items-center justify-center text-2xl"><i class="ri-whatsapp-line"></i></span>
            <div>
                <p class="font-bold text-brand-navy">Fonnte WhatsApp Gateway</p>
                <p class="text-xs text-slate-400">Kirim notifikasi via API/token Fonnte.</p>
            </div>
        </div>
        <span class="flex items-center gap-2 text-xs font-bold px-3 py-1.5 rounded-full {{ $connected ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
            <span class="h-2 w-2 rounded-full {{ $connected ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
            {{ $connected ? 'Terhubung' : 'Belum Terhubung' }}
        </span>
    </div>

    {{-- Konfigurasi --}}
    <div class="bg-white rounded-2xl border border-slate-200 p-5">
        <h3 class="font-bold text-brand-navy mb-4">Konfigurasi Akun</h3>
        <form method="POST" action="{{ route('app.whatsapp.update') }}" class="space-y-3">
            @csrf @method('PUT')
            <div>
                <label class="text-xs font-bold text-slate-500 uppercase">Fonnte API Token / App Key</label>
                <input name="whatsapp_token" value="{{ $branding['whatsapp_token'] }}" placeholder="Tempel token Fonnte Anda" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm font-mono">
            </div>
            <div>
                <label class="text-xs font-bold text-slate-500 uppercase">Nomor Pengirim (Device)</label>
                <input name="whatsapp_sender" value="{{ $branding['whatsapp_sender'] }}" placeholder="0812xxxxxxxx" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm">
            </div>
            <button class="rounded-xl bg-brand-navy text-white px-5 py-2.5 text-sm font-bold hover:bg-brand-navy-dark">Simpan Konfigurasi</button>
        </form>
    </div>

    {{-- Uji kirim --}}
    <div class="bg-white rounded-2xl border border-slate-200 p-5">
        <h3 class="font-bold text-brand-navy mb-4">Uji Coba Kirim Pesan</h3>
        <form method="POST" action="{{ route('app.whatsapp.test') }}" class="space-y-3">
            @csrf
            <div>
                <label class="text-xs font-bold text-slate-500 uppercase">Nomor Tujuan</label>
                <input name="target" placeholder="0812xxxxxxxx" required class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm">
            </div>
            <div>
                <label class="text-xs font-bold text-slate-500 uppercase">Pesan</label>
                <textarea name="message" rows="3" required class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm">Assalamu'alaikum, ini pesan uji coba dari {{ $branding['pondok_name'] }}.</textarea>
            </div>
            <button class="rounded-xl bg-brand-green text-white px-5 py-2.5 text-sm font-bold hover:bg-brand-green-dark"><i class="ri-send-plane-line"></i> Kirim Uji Coba</button>
        </form>
        <p class="text-[11px] text-slate-400 mt-3"><i class="ri-information-line"></i> Pesan dikirim sungguhan ke API Fonnte. Pastikan token & device valid.</p>
    </div>
</div>
@endsection
