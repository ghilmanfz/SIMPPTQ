@php($cardLogoUrl = \App\Support\Branding::logoImageUrl())
{{-- Kartu digital santri (server-rendered). Dipakai di halaman cetak tunggal & massal. --}}
<div class="santri-card bg-gradient-to-b from-brand-navy via-[#0d276b] to-brand-navy-dark p-5 text-white flex flex-col gap-4 shadow-xl relative overflow-hidden" style="width:90mm;height:130mm">
    <div class="absolute -top-20 -right-20 h-40 w-40 rounded-full bg-brand-green/25 blur-xl"></div>
    <div class="absolute -bottom-20 -left-20 h-40 w-40 rounded-full bg-brand-teal/20 blur-xl"></div>

    <div class="flex items-center gap-2.5 pb-3 border-b border-white/10 z-10">
        @if ($branding['logo_type'] === 'image' && $cardLogoUrl)
            <div class="h-8 w-8 rounded-lg overflow-hidden bg-white flex items-center justify-center shadow-sm shrink-0"><img src="{{ $cardLogoUrl }}" alt="Logo" class="h-full w-full object-cover"></div>
        @else
            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-green text-white font-bold text-sm shadow-sm shrink-0">{{ $branding['logo_text'] }}</div>
        @endif
        <div>
            <span class="text-xs font-bold block leading-none">{{ $branding['pondok_name'] }}</span>
            <span class="text-[8px] uppercase tracking-wider text-brand-green block mt-0.5">Kartu Digital Santri</span>
        </div>
    </div>

    <div class="flex flex-col items-center space-y-3 z-10 pt-1">
        <div class="h-28 w-24 rounded-2xl bg-slate-800 border-2 border-brand-green/50 flex items-center justify-center text-white/50 text-4xl overflow-hidden shadow-inner">
            @if ($santri->photoUrl())<img src="{{ $santri->photoUrl() }}" class="h-full w-full object-cover">@else<i class="ri-user-3-fill"></i>@endif
        </div>
        <div class="text-center">
            <h4 class="text-sm font-extrabold tracking-tight">{{ $santri->name }}</h4>
            <span class="text-[10px] text-brand-sky/80 uppercase font-semibold block mt-0.5">Kelas {{ $santri->kelas?->name ?? '-' }}</span>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-2 text-center text-[9px] font-bold text-brand-sky/60 z-10 bg-white/5 p-2.5 border border-white/5 rounded-xl">
        <div><span class="block">NOMOR INDUK (NIS)</span><span class="text-white text-xs">{{ $santri->nis }}</span></div>
        <div><span class="block">STATUS SANTRI</span><span class="text-xs {{ $santri->status === 'Aktif' ? 'text-brand-green' : 'text-amber-300' }}">{{ $santri->status }}</span></div>
    </div>

    <div class="flex flex-col items-center space-y-1.5 z-10 border-t border-white/10 pt-3 mt-auto">
        <div class="santri-card-qr p-2 bg-white rounded-xl shadow-md flex items-center justify-center" data-token="{{ $santri->card_token }}"></div>
        <span class="text-[8px] font-bold text-brand-sky/60 uppercase tracking-widest">TOKEN: {{ $santri->card_token }}</span>
        <span class="text-[8px] text-brand-sky/40">Pindai untuk presensi</span>
    </div>
</div>
