@extends('layouts.app')
@section('title', 'Detail Personil')

@section('content')
<div class="max-w-4xl space-y-6">
    <a href="{{ route('app.personil.index') }}" class="text-xs font-bold text-slate-500 hover:text-brand-navy"><i class="ri-arrow-left-line"></i> Kembali</a>

    <div class="grid md:grid-cols-3 gap-6">
        {{-- Profil --}}
        <div class="md:col-span-1">
            <div class="bg-white rounded-2xl border border-slate-200 p-5 text-center">
                <div class="h-24 w-24 mx-auto rounded-2xl bg-brand-sky text-brand-navy flex items-center justify-center text-3xl font-bold overflow-hidden">
                    @if ($personil->photoUrl())<img src="{{ $personil->photoUrl() }}" class="h-full w-full object-cover">@else {{ strtoupper(mb_substr($personil->name, 0, 1)) }} @endif
                </div>
                <h2 class="mt-3 font-bold text-brand-navy">{{ $personil->name }}</h2>
                <p class="text-xs text-slate-400">{{ $personil->jabatan }}</p>
                <div class="mt-3 flex justify-center gap-2">
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-brand-sky text-brand-navy">{{ $personil->fungsi_kerja }}</span>
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-slate-100 text-slate-600">{{ $personil->status_kerja }}</span>
                </div>
            </div>
        </div>

        {{-- Info --}}
        <div class="md:col-span-2 space-y-6">
            <div class="bg-white rounded-2xl border border-slate-200 p-5">
                <h3 class="font-bold text-brand-navy mb-3">Informasi</h3>
                <dl class="grid grid-cols-2 gap-3 text-sm">
                    <div><dt class="text-[11px] text-slate-400 uppercase">NIK</dt><dd class="text-slate-700">{{ $personil->nik ?? '-' }}</dd></div>
                    <div><dt class="text-[11px] text-slate-400 uppercase">Unit Kerja</dt><dd class="text-slate-700">{{ $personil->unit_kerja ?? '-' }}</dd></div>
                    <div><dt class="text-[11px] text-slate-400 uppercase">No. HP</dt><dd class="text-slate-700">{{ $personil->phone ?? '-' }}</dd></div>
                    <div><dt class="text-[11px] text-slate-400 uppercase">Email</dt><dd class="text-slate-700">{{ $personil->email ?? '-' }}</dd></div>
                    <div><dt class="text-[11px] text-slate-400 uppercase">Akun</dt><dd class="text-slate-700">{{ $personil->user?->email ?? 'Belum ada akun' }}</dd></div>
                    <div><dt class="text-[11px] text-slate-400 uppercase">Alamat</dt><dd class="text-slate-700">{{ $personil->address ?? '-' }}</dd></div>
                </dl>
            </div>

            {{-- Dokumen --}}
            <div class="bg-white rounded-2xl border border-slate-200 p-5">
                <h3 class="font-bold text-brand-navy mb-3">Dokumen Pendukung</h3>
                <div class="space-y-2">
                    @forelse ($personil->documents as $doc)
                        <div class="flex items-center justify-between rounded-xl border border-slate-100 px-3 py-2">
                            <span class="text-sm text-slate-700 flex items-center gap-2"><i class="ri-file-text-line text-brand-navy"></i> {{ $doc->name }}</span>
                            <div class="flex gap-2">
                                <a href="{{ route('app.personil.documents.download', $doc) }}" class="text-xs font-bold text-brand-green hover:underline">Unduh</a>
                                @if ($canManage)
                                    <form method="POST" action="{{ route('app.personil.documents.destroy', $doc) }}" data-confirm="Hapus dokumen ini?" data-confirm-title="Hapus Dokumen" data-confirm-danger>@csrf @method('DELETE')<button class="text-xs font-bold text-red-500 hover:underline">Hapus</button></form>
                                @endif
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-slate-400">Belum ada dokumen.</p>
                    @endforelse
                </div>
                @if ($canManage)
                    <form method="POST" action="{{ route('app.personil.documents.store', $personil) }}" enctype="multipart/form-data" class="mt-4 flex gap-2">
                        @csrf
                        <input name="name" placeholder="Nama dokumen" required class="flex-1 rounded-xl border border-slate-200 px-3 py-2 text-sm">
                        <input type="file" name="file" required class="text-xs text-slate-500 file:mr-2 file:rounded-lg file:border-0 file:bg-slate-100 file:px-2 file:py-2 file:text-xs">
                        <button class="rounded-xl bg-brand-navy text-white px-4 py-2 text-sm font-bold hover:bg-brand-navy-dark shrink-0">Unggah</button>
                    </form>
                @endif
            </div>

            {{-- Jadwal --}}
            @if ($personil->jadwals->isNotEmpty())
                <div class="bg-white rounded-2xl border border-slate-200 p-5">
                    <h3 class="font-bold text-brand-navy mb-3">Jadwal Mengajar</h3>
                    <div class="space-y-2">
                        @foreach ($personil->jadwals as $j)
                            <div class="flex items-center justify-between text-sm border-b border-slate-50 last:border-0 py-1.5">
                                <span class="text-slate-700">{{ $j->day }} · {{ $j->mapel?->name ?? 'Halaqah' }} ({{ $j->kelas?->name }})</span>
                                <span class="text-slate-400 text-xs">{{ jam($j->start_time) }}–{{ jam($j->end_time) }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
