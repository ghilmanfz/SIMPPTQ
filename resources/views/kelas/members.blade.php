@extends('layouts.app')
@section('title', 'Kelola Santri — '.$kela->name)

@section('content')
<div x-data="membersModule()" class="space-y-5">
    <a href="{{ route('app.kelas.index') }}" class="text-xs font-bold text-slate-500 hover:text-brand-navy"><i class="ri-arrow-left-line"></i> Kembali ke Daftar Kelas</a>

    {{-- Header kelas --}}
    <div class="bg-white rounded-2xl border border-slate-200 p-5 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h2 class="text-lg font-bold text-brand-navy">{{ $kela->name }}</h2>
            <p class="text-xs text-slate-400">Tingkat {{ $kela->tingkat ?? '-' }} · {{ $kela->tahunAjaran?->name ?? 'Tanpa TA' }} · Wali: {{ $kela->waliKelas?->name ?? 'Belum ada' }}</p>
        </div>
        <span class="text-sm font-bold text-brand-navy bg-brand-sky rounded-xl px-4 py-2">{{ $members->count() }} santri</span>
    </div>

    <div class="grid lg:grid-cols-2 gap-5">
        {{-- ===== Anggota saat ini ===== --}}
        <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                <h3 class="font-bold text-brand-navy">Anggota Kelas</h3>
                @if ($canManage && $members->isNotEmpty())
                    <label class="text-xs text-slate-500 flex items-center gap-1.5"><input type="checkbox" @change="toggleAllMembers($event)" :checked="allMembersChecked" class="rounded border-slate-300 text-brand-navy"> Pilih semua</label>
                @endif
            </div>

            {{-- Bar aksi anggota terpilih --}}
            @if ($canManage)
                <div x-show="picked.length" x-cloak class="px-5 py-3 bg-brand-navy text-white flex flex-wrap items-center gap-2">
                    <span class="text-xs font-bold"><span x-text="picked.length"></span> dipilih</span>
                    <form method="POST" action="{{ route('app.santri.bulk-move-class') }}" class="flex items-center gap-1 ml-auto" data-confirm-title="Pindah Kelas" data-confirm-label="Ya, Pindahkan" :data-confirm="'Pindahkan ' + picked.length + ' santri ke kelas lain?'">
                        @csrf
                        <template x-for="id in picked" :key="'mv'+id"><input type="hidden" name="ids[]" :value="id"></template>
                        <select name="kelas_id" required class="rounded-lg bg-white/15 text-white px-2 py-1.5 text-xs font-semibold border-0 [&>option]:text-slate-700">
                            <option value="">Pindah ke…</option>
                            @foreach ($kelasList as $k)<option value="{{ $k->id }}">{{ $k->name }}</option>@endforeach
                        </select>
                        <button class="rounded-lg bg-white/15 hover:bg-white/25 px-3 py-1.5 text-xs font-bold">Pindah</button>
                    </form>
                    <form method="POST" action="{{ route('app.santri.bulk-move-class') }}" data-confirm="Keluarkan santri terpilih dari kelas ini? Mereka menjadi tanpa kelas." data-confirm-title="Keluarkan dari Kelas" data-confirm-danger>
                        @csrf
                        <template x-for="id in picked" :key="'rm'+id"><input type="hidden" name="ids[]" :value="id"></template>
                        <button class="rounded-lg bg-red-400/90 hover:bg-red-400 px-3 py-1.5 text-xs font-bold">Keluarkan</button>
                    </form>
                </div>
            @endif

            <div class="divide-y divide-slate-100 max-h-[28rem] overflow-y-auto">
                @forelse ($members as $m)
                    <label class="flex items-center gap-3 px-5 py-3 hover:bg-slate-50 cursor-pointer">
                        @if ($canManage)<input type="checkbox" value="{{ $m->id }}" x-model.number="picked" class="rounded border-slate-300 text-brand-navy">@endif
                        <div class="h-9 w-9 rounded-full bg-brand-sky text-brand-navy flex items-center justify-center text-xs font-bold overflow-hidden">
                            @if ($m->photoUrl())<img src="{{ $m->photoUrl() }}" class="h-full w-full object-cover">@else {{ strtoupper(mb_substr($m->name,0,1)) }} @endif
                        </div>
                        <div class="min-w-0">
                            <p class="font-semibold text-brand-navy text-sm truncate">{{ $m->name }}</p>
                            <p class="text-[11px] text-slate-400">NIS {{ $m->nis }} · {{ $m->status }}</p>
                        </div>
                    </label>
                @empty
                    <div class="px-5 py-10 text-center text-slate-400 text-sm">Belum ada santri di kelas ini.</div>
                @endforelse
            </div>
        </div>

        {{-- ===== Tambah santri ===== --}}
        @if ($canManage)
            <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden flex flex-col">
                <div class="px-5 py-4 border-b border-slate-100">
                    <h3 class="font-bold text-brand-navy">Tambah Santri ke Kelas</h3>
                    <p class="text-[11px] text-slate-400">Santri aktif dari kelas lain / tanpa kelas. Memindahkan akan mengganti kelas lamanya.</p>
                </div>
                <form method="POST" action="{{ route('app.kelas.members.add', $kela) }}" class="flex flex-col flex-1">
                    @csrf
                    <div class="p-4">
                        <div class="relative">
                            <i class="ri-search-line absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                            <input x-model="q" placeholder="Cari nama / NIS santri..." class="w-full rounded-xl border border-slate-200 pl-9 pr-3 py-2 text-sm">
                        </div>
                    </div>
                    <div class="divide-y divide-slate-100 max-h-80 overflow-y-auto border-t border-slate-100">
                        @forelse ($available as $a)
                            <label x-show="match(@js($a->name.' '.$a->nis))" class="flex items-center gap-3 px-5 py-2.5 hover:bg-slate-50 cursor-pointer">
                                <input type="checkbox" name="santri_ids[]" value="{{ $a->id }}" x-model.number="adding" class="rounded border-slate-300 text-brand-navy">
                                <div class="min-w-0">
                                    <p class="font-semibold text-brand-navy text-sm truncate">{{ $a->name }}</p>
                                    <p class="text-[11px] text-slate-400">NIS {{ $a->nis }} · {{ $a->kelas?->name ?? 'Tanpa kelas' }}</p>
                                </div>
                            </label>
                        @empty
                            <div class="px-5 py-10 text-center text-slate-400 text-sm">Tidak ada santri yang bisa ditambahkan.</div>
                        @endforelse
                    </div>
                    <div class="mt-auto p-4 border-t border-slate-100 flex items-center justify-between">
                        <span class="text-xs text-slate-500"><span x-text="adding.length"></span> dipilih</span>
                        <button :disabled="!adding.length" class="rounded-xl bg-brand-navy text-white px-5 py-2 text-sm font-bold hover:bg-brand-navy-dark disabled:opacity-40 disabled:cursor-not-allowed"><i class="ri-user-add-line"></i> Tambahkan ke {{ $kela->name }}</button>
                    </div>
                </form>
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
    function membersModule() {
        return {
            picked: [],
            adding: [],
            q: '',
            memberIds: @json($members->pluck('id')),
            get allMembersChecked() { return this.memberIds.length > 0 && this.memberIds.every(id => this.picked.includes(id)); },
            toggleAllMembers(e) { this.picked = e.target.checked ? [...this.memberIds] : []; },
            match(text) { return !this.q || text.toLowerCase().includes(this.q.toLowerCase()); },
        };
    }
</script>
@endpush
