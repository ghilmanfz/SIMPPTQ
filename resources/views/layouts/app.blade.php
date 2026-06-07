@php($logoUrl = \App\Support\Branding::logoImageUrl())
@php($me = auth()->user())
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — {{ $branding['pondok_name'] }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet" />
    <script defer src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>[x-cloak]{display:none!important;}</style>
    @stack('styles')
</head>
<body class="font-sans antialiased bg-slate-100 text-slate-800 h-screen overflow-hidden" x-data="{ sidebar: false }">
<div class="flex h-screen">

    <!-- ============ SIDEBAR ============ -->
    <div x-show="sidebar" @click="sidebar=false" class="fixed inset-0 bg-black/40 z-30 lg:hidden" x-cloak></div>

    <aside class="fixed lg:static inset-y-0 left-0 z-40 w-64 bg-brand-navy-dark text-white flex flex-col transition-transform duration-200 lg:translate-x-0"
           :class="sidebar ? 'translate-x-0' : '-translate-x-full'">
        <!-- Brand -->
        <div class="h-16 flex items-center gap-3 px-5 border-b border-white/10 shrink-0">
            @if ($branding['logo_type'] === 'image' && $logoUrl)
                <div class="h-9 w-9 rounded-xl overflow-hidden bg-white/10 flex items-center justify-center">
                    <img src="{{ $logoUrl }}" alt="Logo" class="h-full w-full object-cover">
                </div>
            @else
                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-brand-green to-brand-teal text-white font-bold shadow">{{ $branding['logo_text'] }}</div>
            @endif
            <div class="min-w-0">
                <p class="text-sm font-bold leading-none truncate">{{ $branding['pondok_name'] }}</p>
                <p class="text-[10px] text-slate-400 uppercase tracking-wider mt-1">{{ $branding['pondok_tagline'] }}</p>
            </div>
        </div>

        <!-- Nav -->
        <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-1">
            <p class="px-3 pt-1 pb-2 text-[10px] font-bold uppercase tracking-wider text-slate-500">Utama</p>
            <x-nav-link route="app.dashboard" pattern="app.dashboard" icon="ri-dashboard-line">Dashboard</x-nav-link>
            @can('announcement_view')
                <x-nav-link route="app.announcements.index" pattern="app.announcements.*" icon="ri-megaphone-line">Pengumuman</x-nav-link>
            @endcan

            @canany(['presence_gps','schedule_view','leave_apply','leave_approve','swap_apply','swap_approve','payroll_view'])
                <p class="px-3 pt-4 pb-2 text-[10px] font-bold uppercase tracking-wider text-slate-500">Operasional Saya</p>
                @can('presence_gps')
                    <x-nav-link route="app.presensi.index" pattern="app.presensi.index" icon="ri-fingerprint-line">Presensi GPS</x-nav-link>
                @endcan
                @can('schedule_view')
                    <x-nav-link route="app.jadwal.index" pattern="app.jadwal.*" icon="ri-calendar-2-line">Jadwal Mengajar</x-nav-link>
                @endcan
                @if ($me->canAny(['leave_apply','leave_approve']))
                    <x-nav-link route="app.leaves.index" pattern="app.leaves.*" icon="ri-file-list-3-line">Izin / Cuti</x-nav-link>
                @endif
                @if (($me->can('swap_apply') && $me->isPengajar()) || $me->can('swap_approve'))
                    <x-nav-link route="app.swaps.index" pattern="app.swaps.*" icon="ri-swap-box-line">Tukar Jam</x-nav-link>
                @endif
                @can('payroll_view')
                    <x-nav-link route="app.payroll.slip" pattern="app.payroll.slip" icon="ri-bill-line">Slip Gaji Saya</x-nav-link>
                @endcan
            @endcanany

            @canany(['santri_view','santri_presence','class_view','behavior_log','grade_log','visit_log'])
                <p class="px-3 pt-4 pb-2 text-[10px] font-bold uppercase tracking-wider text-slate-500">Santri &amp; Akademik</p>
                @can('santri_view')
                    <x-nav-link route="app.santri.index" pattern="app.santri.*" icon="ri-group-line">Data Santri</x-nav-link>
                @endcan
                @can('santri_presence')
                    <x-nav-link route="app.santri-presensi.index" pattern="app.santri-presensi.*" icon="ri-qr-scan-2-line">Presensi Santri</x-nav-link>
                @endcan
                @can('class_view')
                    <x-nav-link route="app.kelas.index" pattern="app.kelas.*" icon="ri-home-4-line">Kelas / Rombel</x-nav-link>
                @endcan
                @can('behavior_log')
                    <x-nav-link route="app.behaviors.index" pattern="app.behaviors.*" icon="ri-shield-star-line">Perilaku &amp; Poin</x-nav-link>
                @endcan
                @can('grade_log')
                    <x-nav-link route="app.grades.index" pattern="app.grades.*" icon="ri-award-line">Nilai &amp; Perkembangan</x-nav-link>
                @endcan
                @can('visit_log')
                    <x-nav-link route="app.visits.index" pattern="app.visits.*" icon="ri-parent-line">Kunjungan Wali</x-nav-link>
                @endcan
            @endcanany

            @canany(['personnel_view','academic_manage','location_manage','presence_manage','payroll_manage','user_manage','role_manage','setting_manage'])
                <p class="px-3 pt-4 pb-2 text-[10px] font-bold uppercase tracking-wider text-slate-500">Administrasi Pondok</p>
                @can('personnel_view')
                    <x-nav-link route="app.personil.index" pattern="app.personil.*" icon="ri-team-line">Data Personil</x-nav-link>
                @endcan
                @can('academic_manage')
                    <x-nav-link route="app.mapel.index" pattern="app.mapel.*" icon="ri-book-open-line">Master Akademik</x-nav-link>
                @endcan
                @can('location_manage')
                    <x-nav-link route="app.lokasi.index" pattern="app.lokasi.*" icon="ri-map-pin-2-line">Lokasi Presensi</x-nav-link>
                @endcan
                @can('presence_manage')
                    <x-nav-link route="app.presensi.rekap" pattern="app.presensi.rekap" icon="ri-calendar-check-line">Rekap Presensi</x-nav-link>
                @endcan
                @can('payroll_manage')
                    <x-nav-link route="app.payroll.index" pattern="app.payroll.index" icon="ri-money-dollar-circle-line">Penggajian</x-nav-link>
                @endcan
                @can('user_manage')
                    <x-nav-link route="app.users.index" pattern="app.users.*" icon="ri-user-settings-line">Manajemen User</x-nav-link>
                @endcan
                @can('role_manage')
                    <x-nav-link route="app.roles.index" pattern="app.roles.*" icon="ri-lock-2-line">Role &amp; Hak Akses</x-nav-link>
                @endcan
                @can('setting_manage')
                    <x-nav-link route="app.whatsapp.index" pattern="app.whatsapp.*" icon="ri-whatsapp-line">Integrasi WhatsApp</x-nav-link>
                    <x-nav-link route="app.branding.edit" pattern="app.branding.*" icon="ri-palette-line">Branding &amp; Landing</x-nav-link>
                @endcan
            @endcanany

            @can('reports_view')
                <p class="px-3 pt-4 pb-2 text-[10px] font-bold uppercase tracking-wider text-slate-500">Laporan</p>
                <x-nav-link route="app.reports.index" pattern="app.reports.*" icon="ri-bar-chart-box-line">Laporan Strategis</x-nav-link>
            @endcan
        </nav>

        <!-- Footer akun -->
        <div class="border-t border-white/10 p-3 shrink-0">
            <a href="{{ route('app.profile.edit') }}" class="flex items-center gap-3 p-2 rounded-xl hover:bg-white/10 transition-colors">
                <div class="h-9 w-9 rounded-full bg-brand-green/20 text-brand-teal flex items-center justify-center text-xs font-bold">{{ $me->initials() }}</div>
                <div class="min-w-0">
                    <p class="text-xs font-bold truncate">{{ $me->name }}</p>
                    <p class="text-[10px] text-slate-400 truncate">{{ $me->role?->label }}</p>
                </div>
            </a>
        </div>
    </aside>

    <!-- ============ KONTEN ============ -->
    <div class="flex-1 flex flex-col min-w-0">
        <!-- Topbar -->
        <header class="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-4 lg:px-6 shrink-0">
            <div class="flex items-center gap-3 min-w-0">
                <button @click="sidebar=true" class="lg:hidden text-slate-500 text-xl"><i class="ri-menu-line"></i></button>
                <div class="min-w-0">
                    <h1 class="text-base lg:text-lg font-bold text-brand-navy truncate">@yield('title', 'Dashboard')</h1>
                    <p class="text-[11px] text-slate-400 hidden sm:block">{{ tgl(now(), true) }}</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                @yield('header-actions')
                <div class="relative" x-data="{ open:false }">
                    <button @click="open=!open" class="flex items-center gap-2 rounded-xl border border-slate-200 px-2.5 py-1.5 hover:bg-slate-50">
                        <div class="h-7 w-7 rounded-full bg-brand-navy text-white flex items-center justify-center text-[10px] font-bold">{{ $me->initials() }}</div>
                        <span class="text-xs font-semibold text-slate-600 hidden sm:block">{{ $me->name }}</span>
                        <i class="ri-arrow-down-s-line text-slate-400"></i>
                    </button>
                    <div x-show="open" @click.outside="open=false" x-transition x-cloak class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-lg border border-slate-100 py-1.5 z-50">
                        <div class="px-3 py-2 border-b border-slate-100">
                            <p class="text-xs font-bold text-slate-700 truncate">{{ $me->name }}</p>
                            <p class="text-[10px] text-slate-400 truncate">{{ $me->email }}</p>
                        </div>
                        <a href="{{ route('app.profile.edit') }}" class="flex items-center gap-2 px-3 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-50"><i class="ri-user-line"></i> Profil Saya</a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="w-full flex items-center gap-2 px-3 py-2 text-xs font-semibold text-red-500 hover:bg-red-50"><i class="ri-logout-box-r-line"></i> Keluar</button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main -->
        <main class="flex-1 overflow-y-auto p-4 lg:p-6">
            @yield('content')
        </main>
    </div>
</div>

<!-- ============ TOAST ============ -->
<div x-data="toastHost()" x-init="init()" class="fixed top-5 right-5 z-[100] space-y-2 w-80 max-w-[90vw]">
    <template x-for="t in toasts" :key="t.id">
        <div x-transition.opacity class="rounded-xl shadow-lg px-4 py-3 text-sm font-semibold flex items-start gap-2 text-white"
             :class="t.type==='error' ? 'bg-red-500' : (t.type==='warning' ? 'bg-amber-500' : 'bg-brand-green')">
            <i class="mt-0.5" :class="t.type==='error' ? 'ri-close-circle-line' : (t.type==='warning' ? 'ri-error-warning-line' : 'ri-checkbox-circle-line')"></i>
            <span x-text="t.message"></span>
        </div>
    </template>
</div>

<!-- ============ MODAL KONFIRMASI GLOBAL ============ -->
<div x-data="confirmHost()" x-init="init()" x-show="open" x-cloak class="fixed inset-0 z-[200] flex items-center justify-center p-4">
    <div @click="cancel()" class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm"></div>
    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6" x-show="open" x-transition>
        <div class="flex items-start gap-4">
            <div class="h-11 w-11 rounded-full flex items-center justify-center shrink-0 text-xl" :class="danger ? 'bg-red-100 text-red-500' : 'bg-amber-100 text-amber-500'">
                <i :class="danger ? 'ri-alert-line' : 'ri-question-line'"></i>
            </div>
            <div class="min-w-0">
                <h3 class="font-bold text-brand-navy" x-text="title"></h3>
                <p class="text-sm text-slate-500 mt-1 break-words" x-text="text"></p>
            </div>
        </div>
        <div class="flex justify-end gap-2 mt-6">
            <button type="button" @click="cancel()" class="rounded-xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-50">Batal</button>
            <button type="button" @click="accept()" class="rounded-xl px-4 py-2 text-sm font-bold text-white shadow-md" :class="danger ? 'bg-red-500 hover:bg-red-600' : 'bg-brand-navy hover:bg-brand-navy-dark'" x-text="label"></button>
        </div>
    </div>
</div>

<script>
    function confirmHost() {
        return {
            open: false, title: 'Konfirmasi', text: '', label: 'Ya, Lanjutkan', danger: false, _resolve: null,
            init() {
                // API programatik: const ok = await window.appConfirm({ title, text, label, danger })
                window.appConfirm = (opts = {}) => new Promise((resolve) => {
                    this.title = opts.title || 'Konfirmasi';
                    this.text = opts.text || 'Apakah Anda yakin?';
                    this.label = opts.label || 'Ya, Lanjutkan';
                    this.danger = !!opts.danger;
                    this.open = true;
                    this._resolve = resolve;
                });
                // Interceptor: setiap form dengan atribut data-confirm dikonfirmasi via modal in-app.
                document.addEventListener('submit', async (e) => {
                    const form = e.target;
                    if (!(form instanceof HTMLFormElement) || !form.hasAttribute('data-confirm') || form._confirmed) return;
                    e.preventDefault();
                    e.stopPropagation();
                    const ok = await window.appConfirm({
                        title: form.dataset.confirmTitle || 'Konfirmasi',
                        text: form.dataset.confirm,
                        label: form.dataset.confirmLabel || 'Ya, Lanjutkan',
                        danger: form.hasAttribute('data-confirm-danger'),
                    });
                    if (ok) { form._confirmed = true; form.submit(); }
                }, true);
            },
            accept() { this.open = false; if (this._resolve) { this._resolve(true); this._resolve = null; } },
            cancel() { this.open = false; if (this._resolve) { this._resolve(false); this._resolve = null; } },
        };
    }

    function toastHost() {
        return {
            toasts: [],
            init() {
                window.notify = (type, msg) => this.push(type, msg);
                @if (session('success')) this.push('success', @json(session('success'))); @endif
                @if (session('error')) this.push('error', @json(session('error'))); @endif
                @if ($errors->any()) this.push('error', @json($errors->first())); @endif
            },
            push(type, msg) {
                const id = Date.now() + Math.random();
                this.toasts.push({ id, type, message: msg });
                setTimeout(() => { this.toasts = this.toasts.filter(t => t.id !== id); }, 4500);
            },
        };
    }
</script>
@stack('scripts')
</body>
</html>
