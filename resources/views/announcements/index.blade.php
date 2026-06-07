@extends('layouts.app')
@section('title', 'Pengumuman')

@section('content')
<div x-data="anncModule()" class="space-y-5">

    @if ($canManage)
        <div class="flex justify-end">
            <button @click="openCreate()" class="rounded-xl bg-brand-navy text-white px-4 py-2.5 text-sm font-bold hover:bg-brand-navy-dark flex items-center gap-2">
                <i class="ri-add-line"></i> Tambah Pengumuman
            </button>
        </div>
    @endif

    <div class="grid gap-4">
        @forelse ($announcements as $a)
            <div class="bg-white rounded-2xl border border-slate-200 p-5">
                <div class="flex items-start justify-between gap-4">
                    <div class="min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <h3 class="font-bold text-brand-navy">{{ $a->title }}</h3>
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full {{ $a->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">{{ $a->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-brand-sky text-brand-navy">Target: {{ $a->target }}</span>
                        </div>
                        <p class="text-sm text-slate-600 mt-2">{{ $a->content }}</p>
                        <p class="text-[11px] text-slate-400 mt-2">{{ tgl($a->published_at) }} · oleh {{ $a->author?->name ?? 'Sistem' }}</p>
                    </div>
                    @if ($canManage)
                        <div class="flex gap-1 shrink-0">
                            <button @click='openEdit(@json($a))' class="h-8 w-8 rounded-lg bg-slate-100 text-slate-600 hover:bg-brand-sky"><i class="ri-pencil-line"></i></button>
                            <form method="POST" action="{{ route('app.announcements.destroy', $a) }}" data-confirm="Hapus pengumuman ini?" data-confirm-title="Hapus Pengumuman" data-confirm-danger>
                                @csrf @method('DELETE')
                                <button class="h-8 w-8 rounded-lg bg-red-50 text-red-500 hover:bg-red-100"><i class="ri-delete-bin-line"></i></button>
                            </form>
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <div class="bg-white rounded-2xl border border-dashed border-slate-300 p-10 text-center text-slate-400">Belum ada pengumuman.</div>
        @endforelse
    </div>

    {{ $announcements->links() }}

    {{-- Modal --}}
    @if ($canManage)
        <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div @click="open=false" class="absolute inset-0 bg-black/50"></div>
            <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-lg p-6">
                <h3 class="font-bold text-brand-navy mb-4" x-text="mode==='create' ? 'Tambah Pengumuman' : 'Edit Pengumuman'"></h3>
                <form :action="actionUrl" method="POST" class="space-y-3">
                    @csrf
                    <template x-if="mode==='edit'"><input type="hidden" name="_method" value="PUT"></template>
                    <div>
                        <label class="text-xs font-bold text-slate-500 uppercase">Judul</label>
                        <input name="title" x-model="form.title" required class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm">
                    </div>
                    <div>
                        <label class="text-xs font-bold text-slate-500 uppercase">Isi</label>
                        <textarea name="content" x-model="form.content" rows="4" required class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm"></textarea>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="text-xs font-bold text-slate-500 uppercase">Target</label>
                            <select name="target" x-model="form.target" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm">
                                <option value="Semua">Semua</option>
                                @foreach ($roles as $r)
                                    <option value="{{ $r->name }}">{{ $r->label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="text-xs font-bold text-slate-500 uppercase">Tanggal Terbit</label>
                            <input type="date" name="published_at" x-model="form.published_at" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm">
                        </div>
                    </div>
                    <label class="flex items-center gap-2 text-sm text-slate-600">
                        <input type="checkbox" name="is_active" value="1" x-model="form.is_active" class="rounded border-slate-300 text-brand-navy"> Aktif
                    </label>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" @click="open=false" class="rounded-xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600">Batal</button>
                        <button class="rounded-xl bg-brand-navy text-white px-5 py-2 text-sm font-bold hover:bg-brand-navy-dark">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
    function anncModule() {
        return {
            open: false,
            mode: 'create',
            form: { id: null, title: '', content: '', target: 'Semua', published_at: '', is_active: true },
            storeUrl: '{{ route('app.announcements.store') }}',
            get actionUrl() { return this.mode === 'create' ? this.storeUrl : '{{ url('app/announcements') }}/' + this.form.id; },
            openCreate() {
                this.mode = 'create';
                this.form = { id: null, title: '', content: '', target: 'Semua', published_at: '{{ now()->toDateString() }}', is_active: true };
                this.open = true;
            },
            openEdit(a) {
                this.mode = 'edit';
                this.form = { id: a.id, title: a.title, content: a.content, target: a.target, published_at: a.published_at ? a.published_at.substring(0, 10) : '', is_active: !!a.is_active };
                this.open = true;
            },
        };
    }
</script>
@endpush
