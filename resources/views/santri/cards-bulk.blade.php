@extends('layouts.app')
@section('title', 'Cetak Kartu Santri (Massal)')

@push('styles')
<style>
    .santri-card {
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
        color-adjust: exact !important;
        break-inside: avoid;
        page-break-inside: avoid;
    }
    @media print {
        @page { size: A4; margin: 8mm; }
        html, body { margin: 0 !important; padding: 0 !important; background: #fff !important; }
        body * { visibility: hidden !important; }
        #printArea, #printArea * { visibility: visible !important; }
        #printArea { position: absolute; left: 0; top: 0; width: 100%; }
        #printArea .santri-card { box-shadow: none !important; }
    }
</style>
@endpush

@section('header-actions')
    <button onclick="window.print()" class="rounded-xl bg-brand-green text-white px-4 py-2 text-xs font-bold hover:bg-brand-green-dark"><i class="ri-printer-line"></i> Cetak Semua ({{ $santri->count() }})</button>
@endsection

@section('content')
<div class="space-y-4">
    <div class="flex items-center justify-between print:hidden">
        <div>
            <p class="text-sm font-semibold text-brand-navy">{{ $santri->count() }} kartu siap dicetak</p>
            <p class="text-[11px] text-slate-400">Ukuran tiap kartu 90 × 130 mm. Aktifkan <b>Background graphics</b> di dialog cetak. Beberapa kartu muat per lembar A4.</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('app.santri.index') }}" class="rounded-xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-50">Kembali</a>
            <button onclick="window.print()" class="rounded-xl bg-brand-green text-white px-5 py-2 text-sm font-bold hover:bg-brand-green-dark"><i class="ri-printer-line"></i> Cetak Semua</button>
        </div>
    </div>

    <div id="printArea" class="flex flex-wrap gap-4 justify-center">
        @foreach ($santri as $s)
            @include('santri._card', ['santri' => $s])
        @endforeach
    </div>
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
