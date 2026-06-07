@extends('layouts.app')
@section('title', 'Branding & Landing Page')

@php($logoUrl = \App\Support\Branding::logoImageUrl())
@php($heroUrl = \App\Support\Branding::heroImageUrl())

@section('content')
<div x-data="brandingModule()" class="grid lg:grid-cols-3 gap-6">
    {{-- Form --}}
    <div class="lg:col-span-2">
        <form method="POST" action="{{ route('app.branding.update') }}" enctype="multipart/form-data" class="space-y-5">
            @csrf @method('PUT')

            {{-- Logo --}}
            <div class="bg-white rounded-2xl border border-slate-200 p-5 space-y-3">
                <h3 class="font-bold text-brand-navy">Logo & Identitas</h3>
                <div class="grid sm:grid-cols-2 gap-3">
                    <div>
                        <label class="text-xs font-bold text-slate-500 uppercase">Tipe Logo</label>
                        <select name="logo_type" x-model="logo_type" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm"><option value="text">Teks (Inisial)</option><option value="image">Gambar (Logo Instansi)</option></select>
                    </div>
                    <div x-show="logo_type==='text'">
                        <label class="text-xs font-bold text-slate-500 uppercase">Teks Logo</label>
                        <input name="logo_text" x-model="logo_text" maxlength="5" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
                    </div>
                    <div x-show="logo_type==='image'" class="sm:col-span-2">
                        <label class="text-xs font-bold text-slate-500 uppercase">File Logo (maks 1MB)</label>
                        <input type="file" name="logo_image_file" accept="image/*" @change="previewLogo($event)" class="mt-1 w-full text-xs text-slate-500 file:mr-3 file:rounded-lg file:border-0 file:bg-slate-100 file:px-3 file:py-2 file:text-xs file:font-semibold">
                        @if ($logoUrl)<label class="flex items-center gap-2 text-xs text-slate-500 mt-2"><input type="checkbox" name="remove_logo_image" value="1" class="rounded border-slate-300"> Hapus logo saat ini</label>@endif
                    </div>
                </div>
                <div class="grid sm:grid-cols-2 gap-3">
                    <div><label class="text-xs font-bold text-slate-500 uppercase">Nama Pondok</label><input name="pondok_name" x-model="pondok_name" required class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm"></div>
                    <div><label class="text-xs font-bold text-slate-500 uppercase">Tagline</label><input name="pondok_tagline" x-model="pondok_tagline" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm"></div>
                </div>
            </div>

            {{-- Hero --}}
            <div class="bg-white rounded-2xl border border-slate-200 p-5 space-y-3">
                <h3 class="font-bold text-brand-navy">Hero Landing Page</h3>
                <div><label class="text-xs font-bold text-slate-500 uppercase">Judul Utama</label><input name="landing_hero_title" x-model="landing_hero_title" required class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm"></div>
                <div><label class="text-xs font-bold text-slate-500 uppercase">Judul Highlight</label><input name="landing_hero_title_highlight" x-model="landing_hero_title_highlight" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm"></div>
                <div><label class="text-xs font-bold text-slate-500 uppercase">Deskripsi</label><textarea name="landing_hero_desc" x-model="landing_hero_desc" rows="3" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm"></textarea></div>
                <div class="grid sm:grid-cols-2 gap-3">
                    <div>
                        <label class="text-xs font-bold text-slate-500 uppercase">Mode Gambar Hero</label>
                        <select name="landing_hero_image" x-model="landing_hero_image" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm"><option value="default">Default (Kartu Glassmorphism)</option><option value="custom">Gambar Kustom</option></select>
                    </div>
                    <div x-show="landing_hero_image==='custom'">
                        <label class="text-xs font-bold text-slate-500 uppercase">File Hero (maks 3MB)</label>
                        <input type="file" name="hero_image_file" accept="image/*" @change="previewHero($event)" class="mt-1 w-full text-xs text-slate-500 file:mr-3 file:rounded-lg file:border-0 file:bg-slate-100 file:px-3 file:py-2 file:text-xs file:font-semibold">
                    </div>
                </div>
            </div>

            {{-- Statistik --}}
            <div class="bg-white rounded-2xl border border-slate-200 p-5">
                <h3 class="font-bold text-brand-navy mb-3">Statistik Landing</h3>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                    <div><label class="text-[11px] font-bold text-slate-500 uppercase">Personil</label><input name="landing_stats_personnel" value="{{ $branding['landing_stats_personnel'] }}" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm"></div>
                    <div><label class="text-[11px] font-bold text-slate-500 uppercase">Santri</label><input name="landing_stats_santri" value="{{ $branding['landing_stats_santri'] }}" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm"></div>
                    <div><label class="text-[11px] font-bold text-slate-500 uppercase">Halaqah</label><input name="landing_stats_halaqah" value="{{ $branding['landing_stats_halaqah'] }}" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm"></div>
                    <div><label class="text-[11px] font-bold text-slate-500 uppercase">Akurasi</label><input name="landing_stats_accuracy" value="{{ $branding['landing_stats_accuracy'] }}" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm"></div>
                </div>
            </div>

            <button class="rounded-xl bg-brand-green text-white px-6 py-3 text-sm font-bold hover:bg-brand-green-dark"><i class="ri-save-line"></i> Simpan &amp; Publikasikan</button>
        </form>
    </div>

    {{-- Live preview --}}
    <div class="lg:col-span-1">
        <div class="sticky top-4 space-y-4">
            <p class="text-xs font-bold text-slate-400 uppercase">Pratinjau Langsung</p>
            {{-- Navbar preview --}}
            <div class="bg-white rounded-2xl border border-slate-200 p-4 flex items-center gap-3">
                <template x-if="logo_type==='text'"><div class="h-9 w-9 rounded-xl bg-brand-navy text-white flex items-center justify-center font-bold text-sm" x-text="logo_text"></div></template>
                <template x-if="logo_type==='image'"><div class="h-9 w-9 rounded-xl overflow-hidden bg-slate-100"><img :src="logoPreview || '{{ $logoUrl }}'" class="h-full w-full object-cover" x-show="logoPreview || '{{ $logoUrl }}'"></div></template>
                <div><p class="text-sm font-bold text-brand-navy" x-text="pondok_name"></p><p class="text-[10px] text-brand-green uppercase" x-text="pondok_tagline"></p></div>
            </div>
            {{-- Hero preview --}}
            <div class="bg-gradient-to-br from-brand-navy to-brand-navy-dark rounded-2xl p-5 text-white">
                <h3 class="text-lg font-extrabold leading-tight"><span x-text="landing_hero_title"></span> <span class="text-brand-teal" x-text="landing_hero_title_highlight"></span></h3>
                <p class="text-[11px] text-slate-300 mt-2 line-clamp-4" x-text="landing_hero_desc"></p>
                <template x-if="landing_hero_image==='custom' && (heroPreview || '{{ $heroUrl }}')">
                    <img :src="heroPreview || '{{ $heroUrl }}'" class="mt-3 rounded-xl w-full h-24 object-cover">
                </template>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function brandingModule() {
        return {
            logo_type: @json($branding['logo_type']),
            logo_text: @json($branding['logo_text']),
            pondok_name: @json($branding['pondok_name']),
            pondok_tagline: @json($branding['pondok_tagline']),
            landing_hero_title: @json($branding['landing_hero_title']),
            landing_hero_title_highlight: @json($branding['landing_hero_title_highlight']),
            landing_hero_desc: @json($branding['landing_hero_desc']),
            landing_hero_image: @json($branding['landing_hero_image']),
            logoPreview: '', heroPreview: '',
            previewLogo(e) { const f = e.target.files[0]; if (!f) return; if (f.size > 1024*1024) { window.notify('error','Logo melebihi 1MB.'); e.target.value=''; return; } this.logoPreview = URL.createObjectURL(f); },
            previewHero(e) { const f = e.target.files[0]; if (!f) return; if (f.size > 3*1024*1024) { window.notify('error','Hero melebihi 3MB.'); e.target.value=''; return; } this.heroPreview = URL.createObjectURL(f); },
        };
    }
</script>
@endpush
