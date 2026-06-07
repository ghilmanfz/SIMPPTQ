@extends('layouts.app')
@section('title', 'Penggajian')

@section('content')
<div class="grid lg:grid-cols-3 gap-6">
    <div class="lg:col-span-1">
        <div class="bg-white rounded-2xl border border-slate-200 p-5">
            <h3 class="font-bold text-brand-navy mb-4">Buat Periode</h3>
            <form method="POST" action="{{ route('app.payroll.store') }}" class="space-y-3">
                @csrf
                <div><label class="text-xs font-bold text-slate-500 uppercase">Nama Periode</label><input name="name" placeholder="Gaji Juli 2026" required class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm"></div>
                <div class="grid grid-cols-2 gap-2">
                    <div><label class="text-xs font-bold text-slate-500 uppercase">Mulai</label><input type="date" name="start_date" required class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm"></div>
                    <div><label class="text-xs font-bold text-slate-500 uppercase">Selesai</label><input type="date" name="end_date" required class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm"></div>
                </div>
                <button class="w-full rounded-xl bg-brand-navy text-white px-4 py-2.5 text-sm font-bold hover:bg-brand-navy-dark">Buat Periode</button>
            </form>
        </div>
    </div>

    <div class="lg:col-span-2 space-y-3">
        @forelse ($periods as $p)
            <a href="{{ route('app.payroll.show', $p) }}" class="block bg-white rounded-2xl border border-slate-200 p-5 hover:border-brand-navy transition">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="font-bold text-brand-navy">{{ $p->name }}</p>
                        <p class="text-[11px] text-slate-400">{{ tgl($p->start_date) }} – {{ tgl($p->end_date) }} · {{ $p->payslips_count }} slip</p>
                    </div>
                    <span class="text-[10px] font-bold px-2.5 py-1 rounded-full {{ $p->status === 'Final' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">{{ $p->status }}</span>
                </div>
            </a>
        @empty
            <div class="bg-white rounded-2xl border border-dashed border-slate-300 p-10 text-center text-slate-400">Belum ada periode penggajian.</div>
        @endforelse
    </div>
</div>
@endsection
