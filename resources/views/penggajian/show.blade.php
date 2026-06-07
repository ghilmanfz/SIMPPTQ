@extends('layouts.app')
@section('title', 'Detail Penggajian')

@section('content')
<div class="space-y-5">
    <a href="{{ route('app.payroll.index') }}" class="text-xs font-bold text-slate-500 hover:text-brand-navy"><i class="ri-arrow-left-line"></i> Kembali</a>

    <div class="bg-white rounded-2xl border border-slate-200 p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <div class="flex items-center gap-2">
                <h2 class="font-bold text-brand-navy text-lg">{{ $period->name }}</h2>
                <span class="text-[10px] font-bold px-2.5 py-1 rounded-full {{ $period->status === 'Final' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">{{ $period->status }}</span>
            </div>
            <p class="text-xs text-slate-400">{{ tgl($period->start_date) }} – {{ tgl($period->end_date) }} @if ($period->isFinal()) · difinalisasi {{ tgl($period->finalized_at) }} oleh {{ $period->finalizer?->name }} @endif</p>
        </div>
        @unless ($period->isFinal())
            <div class="flex gap-2">
                <form method="POST" action="{{ route('app.payroll.process', $period) }}">@csrf<button class="rounded-xl border border-brand-navy text-brand-navy px-4 py-2.5 text-sm font-bold hover:bg-brand-sky"><i class="ri-calculator-line"></i> Proses</button></form>
                @if ($period->payslips->isNotEmpty())
                    <a href="{{ route('app.payroll.export', $period) }}" class="rounded-xl bg-brand-green text-white px-4 py-2.5 text-sm font-bold hover:bg-brand-green-dark flex items-center gap-1.5"><i class="ri-file-excel-2-line"></i> Excel</a>
                    <form method="POST" action="{{ route('app.payroll.finalize', $period) }}" data-confirm="Finalisasi penggajian periode ini? Data akan dikunci dan tidak dapat diproses ulang." data-confirm-title="Finalisasi Penggajian" data-confirm-label="Ya, Finalisasi">@csrf<button class="rounded-xl bg-brand-green text-white px-4 py-2.5 text-sm font-bold hover:bg-brand-green-dark"><i class="ri-lock-line"></i> Finalisasi</button></form>
                @endif
            </div>
        @else
            <div class="flex gap-2">
                <a href="{{ route('app.payroll.export', $period) }}" class="rounded-xl bg-brand-green text-white px-4 py-2.5 text-sm font-bold hover:bg-brand-green-dark flex items-center gap-1.5"><i class="ri-file-excel-2-line"></i> Download Excel</a>
                <button onclick="window.print()" class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-bold text-slate-600 hover:bg-slate-50"><i class="ri-printer-line"></i> Cetak</button>
            </div>
        @endunless
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-slate-500 text-xs uppercase">
                    <tr>
                        <th class="text-left px-5 py-3">Personil</th>
                        <th class="text-right px-5 py-3">Gaji Pokok</th>
                        <th class="text-right px-5 py-3">Tunjangan</th>
                        <th class="text-right px-5 py-3">Potongan</th>
                        <th class="text-right px-5 py-3">Hadir/Telat</th>
                        <th class="text-right px-5 py-3">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($period->payslips as $slip)
                        <tr>
                            <td class="px-5 py-3 font-semibold text-brand-navy">{{ $slip->personil?->name }}</td>
                            <td class="px-5 py-3 text-right text-slate-600">{{ rupiah($slip->salary_base) }}</td>
                            <td class="px-5 py-3 text-right text-emerald-600">{{ rupiah($slip->allowance) }}</td>
                            <td class="px-5 py-3 text-right text-red-500">{{ rupiah($slip->deduction + $slip->attendance_deduction) }}</td>
                            <td class="px-5 py-3 text-right text-slate-500">{{ $slip->present_days }}/{{ $slip->late_days }}</td>
                            <td class="px-5 py-3 text-right font-bold text-brand-navy">{{ rupiah($slip->total) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-5 py-10 text-center text-slate-400">Belum diproses. Klik tombol <b>Proses</b> untuk menghitung gaji.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
