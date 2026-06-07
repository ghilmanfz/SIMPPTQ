@extends('layouts.app')
@section('title', 'Kartu Santri')

@push('styles')
<style>
    .santri-card {
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
        color-adjust: exact !important;
    }
    @media print {
        @page { size: 90mm 130mm; margin: 0; }
        html, body { margin: 0 !important; padding: 0 !important; background: #fff !important; }
        body * { visibility: hidden !important; }
        #printableCard, #printableCard * { visibility: visible !important; }
        #printableCard { position: fixed; left: 0; top: 0; margin: 0; }
        #printableCard .santri-card { box-shadow: none !important; }
    }
</style>
@endpush

@section('header-actions')
    @can('santri_manage')
        <form method="POST" action="{{ route('app.santri.regenerate-card', $santri) }}" data-confirm="Buat ulang token kartu? Kartu lama tidak akan valid lagi." data-confirm-title="Token Baru" data-confirm-label="Ya, Buat Ulang">
            @csrf
            <button class="rounded-xl border border-slate-200 px-3 py-2 text-xs font-bold text-slate-600 hover:bg-slate-50"><i class="ri-refresh-line"></i> Token Baru</button>
        </form>
    @endcan
@endsection

@section('content')
<div class="flex flex-col items-center gap-5">
    <div id="printableCard">
        @include('santri._card', ['santri' => $santri])
    </div>

    <div class="flex gap-2 justify-center print:hidden">
        <a href="{{ route('app.santri.index') }}" class="rounded-xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-50">Kembali</a>
        <button onclick="window.print()" class="rounded-xl bg-brand-green text-white px-5 py-2 text-sm font-bold hover:bg-brand-green-dark"><i class="ri-printer-line"></i> Cetak Kartu</button>
    </div>
    <p class="text-[11px] text-slate-400 print:hidden">Ukuran cetak kartu: 90 mm × 130 mm. Aktifkan opsi <b>Background graphics</b> di dialog cetak.</p>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        if (typeof QRCode === 'undefined') return;
        document.querySelectorAll('.santri-card-qr').forEach((el) => {
            if (el.dataset.token) new QRCode(el, { text: el.dataset.token, width: 96, height: 96, correctLevel: QRCode.CorrectLevel.M });
        });
    });
</script>
@endpush
