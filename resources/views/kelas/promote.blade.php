@extends('layouts.app')
@section('title', 'Naik Kelas / Promosi Tahunan')

@section('content')
<div class="space-y-5 max-w-4xl">
    <a href="{{ route('app.kelas.index') }}" class="text-xs font-bold text-slate-500 hover:text-brand-navy"><i class="ri-arrow-left-line"></i> Kembali ke Daftar Kelas</a>

    <div class="bg-amber-50 border border-amber-200 rounded-2xl p-4 flex gap-3">
        <i class="ri-information-line text-amber-500 text-xl shrink-0"></i>
        <div class="text-sm text-amber-800">
            <p class="font-bold">Cara kerja</p>
            <p class="text-xs mt-1">Untuk tiap kelas, pilih aksinya: <b>Naik ke kelas tujuan</b>, <b>Luluskan</b>, atau <b>Tidak diproses</b>. Centang santri yang <b>tinggal kelas</b> (tidak ikut diproses). Semua perubahan tercatat di Riwayat Kelas.</p>
        </div>
    </div>

    @if ($sourceKelas->isEmpty())
        <div class="bg-white rounded-2xl border border-dashed border-slate-300 p-10 text-center text-slate-400">Belum ada kelas yang berisi santri.</div>
    @else
        <form method="POST" action="{{ route('app.kelas.promote.process') }}" data-confirm="Proses naik kelas sekarang? Perubahan kelas & status santri akan diterapkan." data-confirm-title="Proses Naik Kelas" data-confirm-label="Ya, Proses">
            @csrf
            <div class="space-y-4">
                @foreach ($sourceKelas as $k)
                    @php($list = $santriByKelas[$k->id] ?? collect())
                    <div class="bg-white rounded-2xl border border-slate-200 p-5" x-data="{ action: 'skip', show: false }">
                        <div class="flex flex-wrap items-center gap-3 justify-between">
                            <div class="flex items-center gap-3">
                                <span class="h-10 w-10 rounded-xl bg-brand-sky text-brand-navy flex items-center justify-center font-bold">{{ $list->count() }}</span>
                                <div>
                                    <p class="font-bold text-brand-navy">{{ $k->name }}</p>
                                    <p class="text-[11px] text-slate-400">Tingkat {{ $k->tingkat ?? '-' }} · {{ $k->tahunAjaran?->name ?? 'Tanpa TA' }}</p>
                                </div>
                            </div>
                            <div class="flex flex-wrap items-center gap-2">
                                <select name="actions[{{ $k->id }}]" x-model="action" class="rounded-xl border border-slate-200 px-3 py-2 text-sm font-semibold">
                                    <option value="skip">Tidak diproses</option>
                                    <option value="move">Naik ke kelas…</option>
                                    <option value="graduate">Luluskan</option>
                                </select>
                                <select name="targets[{{ $k->id }}]" x-show="action==='move'" x-cloak class="rounded-xl border border-slate-200 px-3 py-2 text-sm" :required="action==='move'">
                                    <option value="">Pilih kelas tujuan</option>
                                    @foreach ($targetKelas as $t)
                                        @if ($t->id !== $k->id)<option value="{{ $t->id }}">{{ $t->name }} ({{ $t->tahunAjaran?->name ?? 'Tanpa TA' }})</option>@endif
                                    @endforeach
                                </select>
                                <span x-show="action==='graduate'" x-cloak class="text-xs font-bold text-emerald-600 px-2"><i class="ri-graduation-cap-line"></i> Status → Lulus</span>
                            </div>
                        </div>

                        @if ($list->isNotEmpty())
                            <div class="mt-3 pt-3 border-t border-slate-100" x-show="action!=='skip'" x-cloak>
                                <button type="button" @click="show=!show" class="text-xs font-bold text-brand-navy hover:underline">
                                    <i class="ri-list-check-2"></i> <span x-text="show ? 'Sembunyikan' : 'Atur per santri'"></span> ({{ $list->count() }})
                                </button>
                                <p class="text-[11px] text-slate-400 mt-1">Centang santri yang <b>tinggal kelas</b> (tidak ikut diproses).</p>
                                <div x-show="show" x-cloak class="mt-2 grid sm:grid-cols-2 gap-1.5">
                                    @foreach ($list as $s)
                                        <label class="flex items-center gap-2 text-sm px-2 py-1.5 rounded-lg hover:bg-slate-50">
                                            <input type="checkbox" name="exclude[]" value="{{ $s->id }}" class="rounded border-slate-300 text-amber-500">
                                            <span class="text-slate-600">{{ $s->name }}</span>
                                            <span class="text-[10px] text-slate-400">NIS {{ $s->nis }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>

            <div class="flex justify-end gap-2 mt-5">
                <a href="{{ route('app.kelas.index') }}" class="rounded-xl border border-slate-200 px-5 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-50">Batal</a>
                <button class="rounded-xl bg-brand-green text-white px-6 py-2.5 text-sm font-bold hover:bg-brand-green-dark shadow-md"><i class="ri-arrow-up-double-line"></i> Proses Naik Kelas</button>
            </div>
        </form>
    @endif
</div>
@endsection
