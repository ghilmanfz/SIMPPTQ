@extends('layouts.app')
@section('title', 'Presensi Santri (Scan)')

@section('content')
<div x-data="scanModule()" class="grid lg:grid-cols-3 gap-6">
    {{-- Panel scan --}}
    <div class="lg:col-span-1 space-y-4">
        <div class="bg-white rounded-2xl border border-slate-200 p-5">
            <h3 class="font-bold text-brand-navy mb-3">Mesin Presensi</h3>
            <label class="text-xs font-bold text-slate-500 uppercase">Kegiatan / Sesi</label>
            <select x-model="kegiatan" class="mt-1 mb-4 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm">
                <option>Halaqah Subuh</option><option>Halaqah Sore</option><option>Sekolah Pagi</option><option>Kegiatan Malam</option>
            </select>

            {{-- Kamera scanner QR (html5-qrcode) --}}
            <div class="relative rounded-xl bg-brand-navy-dark overflow-hidden mb-3">
                {{-- Wadah kamera --}}
                <div id="reader" class="w-full" :class="scanning ? '' : 'hidden'"></div>

                {{-- Placeholder ketika kamera mati --}}
                <div x-show="!scanning" class="relative h-44 flex flex-col items-center justify-center gap-2 text-white/40">
                    <div class="absolute left-4 right-4 h-0.5 bg-brand-green/80 qr-scanner-line"></div>
                    <i class="ri-qr-scan-2-line text-4xl"></i>
                    <span class="text-[11px]" x-text="camError || 'Kamera mati'"></span>
                </div>
            </div>

            {{-- Tombol nyalakan / matikan kamera --}}
            <button type="button" @click="toggleCamera()"
                    class="w-full rounded-xl px-4 py-2.5 text-sm font-bold flex items-center justify-center gap-2 mb-3"
                    :class="scanning ? 'bg-red-50 text-red-500 hover:bg-red-100' : 'bg-brand-navy text-white hover:bg-brand-navy-dark'">
                <i :class="scanning ? 'ri-camera-off-line' : 'ri-camera-line'"></i>
                <span x-text="scanning ? 'Matikan Kamera' : 'Nyalakan Kamera'"></span>
            </button>

            <form method="POST" action="{{ route('app.santri-presensi.scan') }}" x-ref="scanForm">
                @csrf
                <input type="hidden" name="kegiatan" :value="kegiatan">
                <input name="card_token" x-ref="tokenInput" autofocus placeholder="Atau tempel/scan token kartu..." class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm font-mono">
                <button class="mt-2 w-full rounded-xl bg-brand-green text-white px-4 py-2.5 text-sm font-bold hover:bg-brand-green-dark">Catat Kehadiran</button>
            </form>

            <div class="mt-4 pt-4 border-t border-slate-100">
                <label class="text-xs font-bold text-slate-500 uppercase">Input Manual</label>
                <form method="POST" action="{{ route('app.santri-presensi.scan') }}" class="mt-1 flex gap-2">
                    @csrf
                    <input type="hidden" name="kegiatan" :value="kegiatan">
                    <select name="santri_id" required class="flex-1 rounded-xl border border-slate-200 px-3 py-2 text-sm">
                        <option value="">— Pilih santri —</option>
                        @foreach ($santriList as $s)<option value="{{ $s->id }}">{{ $s->name }} ({{ $s->kelas?->name }})</option>@endforeach
                    </select>
                    <button class="rounded-xl bg-brand-navy text-white px-4 py-2 text-sm font-bold hover:bg-brand-navy-dark">Catat</button>
                </form>
            </div>
        </div>
    </div>

    {{-- Daftar hadir hari ini --}}
    <div class="lg:col-span-2">
        <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                <h3 class="font-bold text-brand-navy">Kehadiran Hari Ini</h3>
                <span class="text-xs font-bold text-brand-green">{{ $todayPresences->count() }} santri</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 text-slate-500 text-xs uppercase">
                        <tr><th class="text-left px-5 py-3">Waktu</th><th class="text-left px-5 py-3">Santri</th><th class="text-left px-5 py-3">Kelas</th><th class="text-left px-5 py-3">Kegiatan</th></tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($todayPresences as $p)
                            <tr>
                                <td class="px-5 py-3 font-mono text-xs text-slate-500">{{ jam($p->time) }}</td>
                                <td class="px-5 py-3 font-semibold text-brand-navy">{{ $p->santri?->name }}</td>
                                <td class="px-5 py-3 text-slate-500">{{ $p->kelas?->name ?? '-' }}</td>
                                <td class="px-5 py-3 text-slate-500">{{ $p->kegiatan }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-5 py-10 text-center text-slate-400">Belum ada presensi santri hari ini.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
    function scanModule() {
        return {
            kegiatan: 'Halaqah Subuh',
            scanning: false,
            camError: '',
            scanner: null,
            busy: false,

            async toggleCamera() {
                if (this.scanning) { await this.stop(); return; }
                this.camError = '';

                if (!window.Html5Qrcode) { this.camError = 'Library kamera gagal dimuat.'; return; }
                if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                    this.camError = 'Browser tidak mendukung kamera.';
                    window.notify('error', 'Browser tidak mendukung akses kamera.');
                    return;
                }

                this.scanning = true;
                await this.$nextTick();

                try {
                    this.scanner = new Html5Qrcode('reader', { verbose: false });
                    await this.scanner.start(
                        { facingMode: 'environment' },
                        { fps: 10, qrbox: { width: 200, height: 200 } },
                        (decodedText) => this.onScan(decodedText),
                        () => {} // abaikan frame gagal
                    );
                } catch (e) {
                    this.scanning = false;
                    this.camError = 'Gagal membuka kamera. Izinkan akses.';
                    window.notify('error', 'Gagal membuka kamera. Pastikan izin kamera diberikan.');
                }
            },

            onScan(token) {
                if (this.busy || !token) return;
                this.busy = true;
                // Isi token & submit form presensi
                this.$refs.tokenInput.value = token.trim();
                this.stop().finally(() => this.$refs.scanForm.submit());
            },

            async stop() {
                if (this.scanner) {
                    try { await this.scanner.stop(); await this.scanner.clear(); } catch (e) {}
                    this.scanner = null;
                }
                this.scanning = false;
            },

            destroy() { this.stop(); },
        };
    }
</script>
@endpush
