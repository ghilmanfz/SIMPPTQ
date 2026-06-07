@extends('layouts.app')
@section('title', 'Lokasi Presensi')

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
@endpush

@section('content')
<div x-data="lokasiModule()" class="space-y-5">
    <div class="flex justify-end">
        <button @click="openCreate()" class="rounded-xl bg-brand-navy text-white px-4 py-2.5 text-sm font-bold hover:bg-brand-navy-dark flex items-center gap-2"><i class="ri-map-pin-add-line"></i> Tambah Lokasi</button>
    </div>

    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse ($lokasi as $l)
            <div class="bg-white rounded-2xl border border-slate-200 p-5">
                <div class="flex items-start justify-between">
                    <span class="h-10 w-10 rounded-xl bg-brand-sky text-brand-navy flex items-center justify-center"><i class="ri-map-pin-2-line"></i></span>
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full {{ $l->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">{{ $l->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                </div>
                <p class="font-bold text-brand-navy mt-3">{{ $l->name }}</p>
                <p class="text-[11px] text-slate-400 font-mono">{{ $l->latitude }}, {{ $l->longitude }}</p>
                <p class="text-xs text-slate-500 mt-1">Radius: {{ $l->radius }} meter</p>
                <div class="flex gap-2 mt-4 pt-3 border-t border-slate-100">
                    <button @click='openEdit(@json($l))' class="flex-1 rounded-lg bg-slate-100 text-slate-600 text-xs font-semibold py-2 hover:bg-brand-sky">Edit</button>
                    <form method="POST" action="{{ route('app.lokasi.destroy', $l) }}" data-confirm="Hapus lokasi presensi ini?" data-confirm-title="Hapus Lokasi" data-confirm-danger class="flex-1">@csrf @method('DELETE')<button class="w-full rounded-lg bg-red-50 text-red-500 text-xs font-semibold py-2 hover:bg-red-100">Hapus</button></form>
                </div>
            </div>
        @empty
            <div class="sm:col-span-2 lg:col-span-3 bg-white rounded-2xl border border-dashed border-slate-300 p-10 text-center text-slate-400">Belum ada lokasi presensi.</div>
        @endforelse
    </div>

    {{-- Modal dengan peta interaktif --}}
    <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div @click="open=false" class="absolute inset-0 bg-black/50"></div>
        <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-2xl p-6 max-h-[92vh] overflow-y-auto">
            <h3 class="font-bold text-brand-navy mb-1" x-text="mode==='create' ? 'Tambah Lokasi' : 'Edit Lokasi'"></h3>
            <p class="text-xs text-slate-400 mb-4">Cari tempat, gunakan lokasi Anda, atau geser/klik pin pada peta.</p>

            <form :action="actionUrl" method="POST" class="space-y-3">
                @csrf
                <template x-if="mode==='edit'"><input type="hidden" name="_method" value="PUT"></template>

                <div>
                    <label class="text-xs font-bold text-slate-500 uppercase">Nama Lokasi</label>
                    <input name="name" x-model="form.name" required class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
                </div>

                {{-- Pencarian tempat + GPS --}}
                <div class="flex gap-2">
                    <div class="relative flex-1">
                        <i class="ri-search-line absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                        <input type="text" x-model="searchQuery" @keydown.enter.prevent="search()" placeholder="Cari nama tempat / alamat..." class="w-full rounded-xl border border-slate-200 pl-9 pr-3 py-2 text-sm">
                    </div>
                    <button type="button" @click="search()" class="rounded-xl bg-slate-100 text-slate-600 px-4 py-2 text-sm font-semibold hover:bg-slate-200">Cari</button>
                    <button type="button" @click="useMyLocation()" class="rounded-xl bg-brand-green text-white px-3 py-2 text-sm font-semibold hover:bg-brand-green-dark flex items-center gap-1" title="Gunakan lokasi saya"><i class="ri-focus-3-line"></i><span class="hidden sm:inline">Lokasi Saya</span></button>
                </div>

                {{-- Peta --}}
                <div id="lokasiMap" class="h-64 w-full rounded-xl border border-slate-200 overflow-hidden z-0"></div>

                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="text-xs font-bold text-slate-500 uppercase">Latitude</label>
                        <input name="latitude" x-model="form.latitude" @change="syncFromInputs()" required class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm font-mono">
                    </div>
                    <div>
                        <label class="text-xs font-bold text-slate-500 uppercase">Longitude</label>
                        <input name="longitude" x-model="form.longitude" @change="syncFromInputs()" required class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm font-mono">
                    </div>
                    <div>
                        <label class="text-xs font-bold text-slate-500 uppercase">Radius (m)</label>
                        <input type="number" name="radius" x-model="form.radius" @input="updateRadius()" required class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
                    </div>
                </div>

                <label class="flex items-center gap-2 text-sm text-slate-600"><input type="checkbox" name="is_active" value="1" x-model="form.is_active" class="rounded border-slate-300 text-brand-navy"> Aktif</label>

                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="open=false" class="rounded-xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600">Batal</button>
                    <button class="rounded-xl bg-brand-navy text-white px-5 py-2 text-sm font-bold hover:bg-brand-navy-dark">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    function lokasiModule() {
        return {
            open: false,
            mode: 'create',
            form: { id: null, name: '', latitude: '', longitude: '', radius: 100, is_active: true },
            map: null, marker: null, circle: null, searchQuery: '',
            defaultCenter: [-6.9147440, 107.6098100], // fallback: komplek pondok

            get actionUrl() { return this.mode === 'create' ? '{{ route('app.lokasi.store') }}' : '{{ url('app/lokasi') }}/' + this.form.id; },

            openCreate() {
                this.mode = 'create';
                this.form = { id: null, name: '', latitude: '', longitude: '', radius: 100, is_active: true };
                this.searchQuery = '';
                this.open = true;
                this.$nextTick(() => this.initMap());
            },
            openEdit(l) {
                this.mode = 'edit';
                this.form = { id: l.id, name: l.name, latitude: l.latitude, longitude: l.longitude, radius: l.radius, is_active: !!l.is_active };
                this.searchQuery = '';
                this.open = true;
                this.$nextTick(() => this.initMap());
            },

            initMap() {
                const lat = parseFloat(this.form.latitude) || this.defaultCenter[0];
                const lng = parseFloat(this.form.longitude) || this.defaultCenter[1];

                if (!this.map) {
                    this.map = L.map('lokasiMap').setView([lat, lng], 16);
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        maxZoom: 19, attribution: '&copy; OpenStreetMap',
                    }).addTo(this.map);
                    this.marker = L.marker([lat, lng], { draggable: true }).addTo(this.map);
                    this.circle = L.circle([lat, lng], { radius: this.radiusVal(), color: '#107c41', fillColor: '#107c41', fillOpacity: 0.12, weight: 1 }).addTo(this.map);

                    this.marker.on('drag', (e) => this.setLatLng(e.target.getLatLng().lat, e.target.getLatLng().lng));
                    this.map.on('click', (e) => { this.marker.setLatLng(e.latlng); this.setLatLng(e.latlng.lat, e.latlng.lng); });
                } else {
                    this.map.setView([lat, lng], 16);
                    this.marker.setLatLng([lat, lng]);
                    this.circle.setLatLng([lat, lng]).setRadius(this.radiusVal());
                }
                // Jika lokasi belum diisi (mode tambah), pakai koordinat marker default.
                if (!this.form.latitude) this.setLatLng(lat, lng);
                setTimeout(() => this.map.invalidateSize(), 250);
            },

            radiusVal() { return parseInt(this.form.radius) || 100; },

            setLatLng(lat, lng) {
                this.form.latitude = (+lat).toFixed(7);
                this.form.longitude = (+lng).toFixed(7);
                if (this.circle) this.circle.setLatLng([lat, lng]);
            },

            updateRadius() { if (this.circle) this.circle.setRadius(this.radiusVal()); },

            syncFromInputs() {
                const lat = parseFloat(this.form.latitude), lng = parseFloat(this.form.longitude);
                if (!isNaN(lat) && !isNaN(lng) && this.map) {
                    this.map.setView([lat, lng], 16);
                    this.marker.setLatLng([lat, lng]);
                    this.circle.setLatLng([lat, lng]);
                }
            },

            useMyLocation() {
                if (!navigator.geolocation) { window.notify('error', 'Browser tidak mendukung GPS.'); return; }
                navigator.geolocation.getCurrentPosition(
                    (p) => {
                        const { latitude, longitude } = p.coords;
                        this.map.setView([latitude, longitude], 17);
                        this.marker.setLatLng([latitude, longitude]);
                        this.setLatLng(latitude, longitude);
                        window.notify('success', 'Lokasi Anda berhasil dipakai.');
                    },
                    () => window.notify('error', 'Gagal mengambil lokasi GPS. Izinkan akses lokasi.')
                );
            },

            async search() {
                if (!this.searchQuery.trim()) return;
                try {
                    const res = await fetch('https://nominatim.openstreetmap.org/search?format=json&limit=1&q=' + encodeURIComponent(this.searchQuery));
                    const data = await res.json();
                    if (!data.length) { window.notify('warning', 'Lokasi tidak ditemukan.'); return; }
                    const lat = +data[0].lat, lng = +data[0].lon;
                    this.map.setView([lat, lng], 17);
                    this.marker.setLatLng([lat, lng]);
                    this.setLatLng(lat, lng);
                    if (!this.form.name) this.form.name = (data[0].display_name || '').split(',')[0];
                } catch (e) {
                    window.notify('error', 'Gagal mencari lokasi.');
                }
            },
        };
    }
</script>
@endpush
