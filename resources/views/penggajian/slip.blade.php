@extends('layouts.app')
@section('title', 'Slip Gaji Saya')

@section('content')
<div class="max-w-2xl space-y-5">
    @if (! $personil)
        <div class="bg-amber-50 border border-amber-200 rounded-2xl p-5 text-sm text-amber-700">Akun Anda belum terhubung dengan data personil.</div>
    @elseif ($payslips->isEmpty())
        <div class="bg-white rounded-2xl border border-dashed border-slate-300 p-10 text-center text-slate-400">Belum ada slip gaji final.</div>
    @else
        @foreach ($payslips as $slip)
            <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
                <div class="bg-brand-navy text-white px-5 py-4 flex items-center justify-between">
                    <div>
                        <p class="font-bold">{{ $slip->period->name }}</p>
                        <p class="text-[11px] text-slate-300">{{ $personil->name }} · {{ $personil->jabatan }}</p>
                    </div>
                    <button onclick="window.print()" class="text-xs font-semibold text-slate-200 hover:text-white print:hidden"><i class="ri-printer-line"></i> Cetak</button>
                </div>
                <div class="p-5 space-y-2 text-sm">
                    <div class="flex justify-between"><span class="text-slate-500">Gaji Pokok</span><span class="font-semibold text-slate-700">{{ rupiah($slip->salary_base) }}</span></div>
                    <div class="flex justify-between"><span class="text-slate-500">Tunjangan</span><span class="font-semibold text-emerald-600">+ {{ rupiah($slip->allowance) }}</span></div>
                    <div class="flex justify-between"><span class="text-slate-500">Potongan Tetap</span><span class="font-semibold text-red-500">- {{ rupiah($slip->deduction) }}</span></div>
                    <div class="flex justify-between"><span class="text-slate-500">Potongan Kehadiran ({{ $slip->late_days }}x telat)</span><span class="font-semibold text-red-500">- {{ rupiah($slip->attendance_deduction) }}</span></div>
                    <div class="flex justify-between text-[11px] text-slate-400"><span>Kehadiran</span><span>{{ $slip->present_days }} hari</span></div>
                    <div class="flex justify-between pt-3 mt-2 border-t border-slate-100"><span class="font-bold text-brand-navy">Total Diterima</span><span class="font-extrabold text-brand-navy text-lg">{{ rupiah($slip->total) }}</span></div>
                </div>
            </div>
        @endforeach
    @endif
</div>
@endsection
