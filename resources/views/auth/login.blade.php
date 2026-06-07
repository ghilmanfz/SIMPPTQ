@php($logoUrl = \App\Support\Branding::logoImageUrl())
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk — {{ $branding['pondok_name'] }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet" />
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="font-sans antialiased bg-slate-50 text-slate-800 min-h-screen flex flex-col justify-between overflow-x-hidden">

    <div class="fixed -top-40 -left-40 h-[600px] w-[600px] rounded-full bg-brand-sky/50 blur-[140px] pointer-events-none -z-10"></div>
    <div class="fixed -bottom-40 -right-40 h-[600px] w-[600px] rounded-full bg-brand-green/5 blur-[120px] pointer-events-none -z-10"></div>

    <header class="w-full px-6 py-4 flex justify-between items-center bg-white/40 backdrop-blur-sm border-b border-slate-100">
        <a href="{{ route('landing') }}" class="flex items-center gap-3">
            @if ($branding['logo_type'] === 'image' && $logoUrl)
                <div class="h-9 w-9 rounded-xl overflow-hidden flex items-center justify-center bg-slate-100 border">
                    <img src="{{ $logoUrl }}" alt="Logo" class="h-full w-full object-cover">
                </div>
            @else
                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-brand-navy to-brand-navy-dark text-white font-bold text-base shadow-sm">{{ $branding['logo_text'] }}</div>
            @endif
            <div>
                <span class="text-sm font-bold text-brand-navy block leading-none">{{ $branding['pondok_name'] }}</span>
                <span class="text-[9px] font-medium uppercase tracking-wider text-brand-green">Kembali ke Beranda</span>
            </div>
        </a>
        <a href="{{ route('landing') }}" class="text-xs font-bold text-slate-500 hover:text-brand-navy flex items-center gap-1">
            <i class="ri-arrow-left-line"></i> Beranda Publik
        </a>
    </header>

    <main class="flex-1 flex items-center justify-center p-6">
        <div class="w-full max-w-5xl grid lg:grid-cols-12 bg-white rounded-3xl overflow-hidden shadow-2xl border border-slate-100 min-h-[500px]">

            <!-- Banner kiri -->
            <div class="lg:col-span-5 bg-gradient-to-br from-brand-navy via-[#0c266f] to-brand-navy-dark p-8 text-white flex flex-col justify-between relative overflow-hidden">
                <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top_right,_var(--tw-gradient-stops))] from-brand-green/20 via-transparent to-transparent pointer-events-none"></div>
                <div class="space-y-2 relative z-10">
                    <span class="text-[10px] font-bold uppercase tracking-wider bg-brand-green/25 border border-brand-green/35 px-2.5 py-1 rounded-full text-brand-sky inline-block">Portal Staf</span>
                    <h2 class="text-2xl font-bold tracking-tight">Sistem Manajemen Terintegrasi</h2>
                    <p class="text-xs text-slate-300 leading-relaxed">Kelola administrasi kepondokan, data santri, absensi terverifikasi, penggajian, dan laporan strategis dalam satu atap digital yang aman.</p>
                </div>
                <div class="space-y-4 pt-8 relative z-10">
                    <div class="rounded-2xl bg-white/10 p-4 border border-white/10 backdrop-blur-md">
                        <div class="flex items-center gap-3 text-white mb-2">
                            <i class="ri-map-pin-user-line text-brand-teal text-xl"></i>
                            <h4 class="text-xs font-bold uppercase tracking-wider">Presensi GPS Aktif</h4>
                        </div>
                        <p class="text-[11px] text-slate-200">Check-in &amp; out tervalidasi otomatis oleh koordinat GPS pondok. Tanpa antrean sidik jari.</p>
                    </div>
                    <div class="rounded-2xl bg-white/10 p-4 border border-white/10 backdrop-blur-md">
                        <div class="flex items-center gap-3 text-white mb-2">
                            <i class="ri-qr-code-line text-brand-green text-xl"></i>
                            <h4 class="text-xs font-bold uppercase tracking-wider">Presensi Barcode Santri</h4>
                        </div>
                        <p class="text-[11px] text-slate-200">Santri cukup membawa kartu santri untuk di-scan oleh asatidzah/petugas penanggung jawab.</p>
                    </div>
                </div>
                <p class="text-[10px] text-slate-400 mt-6 relative z-10">Layanan Bantuan IT: info@pptqnuruliman.sch.id</p>
            </div>

            <!-- Form kanan -->
            <div class="lg:col-span-7 p-8 md:p-12 flex flex-col justify-between bg-white" x-data="{ showPass: false }">
                <div class="space-y-6">
                    <div>
                        <h1 class="text-2xl font-extrabold text-brand-navy tracking-tight">Selamat Datang Kembali</h1>
                        <p class="text-xs text-slate-400">Silakan masukkan akun Anda untuk mengakses dashboard manajemen.</p>
                    </div>

                    @if ($errors->any())
                        <div class="rounded-xl bg-red-50 border border-red-100 px-4 py-3 text-xs font-semibold text-red-600 flex items-start gap-2">
                            <i class="ri-error-warning-line text-sm mt-0.5"></i>
                            <span>{{ $errors->first() }}</span>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('login') }}" class="space-y-4" id="loginForm">
                        @csrf
                        <div>
                            <label for="email" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Email</label>
                            <div class="relative rounded-xl shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400"><i class="ri-mail-line"></i></div>
                                <input type="text" id="email" name="email" value="{{ old('email') }}" required autofocus placeholder="staf@nuruliman.net" class="block w-full rounded-xl border border-slate-200 bg-slate-50 pl-10 pr-3 py-3 text-sm text-slate-700 placeholder-slate-400 transition-all focus:border-brand-navy focus:bg-white focus:outline-none focus:ring-1 focus:ring-brand-navy">
                            </div>
                        </div>

                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <label for="password" class="block text-xs font-bold text-slate-500 uppercase tracking-wider">Kata Sandi</label>
                            </div>
                            <div class="relative rounded-xl shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400"><i class="ri-lock-line"></i></div>
                                <input :type="showPass ? 'text' : 'password'" id="password" name="password" required placeholder="••••••••" class="block w-full rounded-xl border border-slate-200 bg-slate-50 pl-10 pr-10 py-3 text-sm text-slate-700 placeholder-slate-400 transition-all focus:border-brand-navy focus:bg-white focus:outline-none focus:ring-1 focus:ring-brand-navy">
                                <button type="button" @click="showPass = !showPass" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-brand-navy">
                                    <i :class="showPass ? 'ri-eye-off-line' : 'ri-eye-line'"></i>
                                </button>
                            </div>
                        </div>

                        <div class="flex items-center">
                            <input id="remember" name="remember" type="checkbox" class="h-4 w-4 rounded border-slate-300 text-brand-navy focus:ring-brand-navy">
                            <label for="remember" class="ml-2.5 block text-xs font-medium text-slate-500">Ingat Sesi Masuk Saya</label>
                        </div>

                        <button type="submit" class="w-full flex justify-center items-center gap-2 rounded-xl bg-brand-navy py-3.5 text-sm font-bold text-white shadow-lg shadow-brand-navy/10 transition-all hover:bg-brand-navy-dark hover:scale-[1.01] active:scale-[0.99] focus:outline-none">
                            Masuk Aplikasi <i class="ri-login-box-line"></i>
                        </button>
                    </form>
                </div>

                <!-- Preset demo: mengisi & submit form dengan kredensial seeder -->
                <div class="mt-8 border-t border-slate-100 pt-6 space-y-3.5">
                    <div class="flex items-center gap-2">
                        <span class="h-1.5 w-1.5 rounded-full bg-brand-green"></span>
                        <h4 class="text-xs font-extrabold tracking-wider text-slate-400 uppercase">Uji Coba Cepat (Akses Demo)</h4>
                    </div>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5">
                        @foreach ([
                            ['superadmin@nuruliman.net', 'superadmin123', 'Super Admin', 'Pengaturan Sistem'],
                            ['petugas@nuruliman.net', 'petugas123', 'Admin Operasional', 'Petugas Data'],
                            ['ustadz.ahmad@nuruliman.net', 'ustadz123', 'Guru (Pengajar)', 'Ustadz / Ustadzah'],
                            ['staff.budiyono@nuruliman.net', 'staff123', 'Staf Non-Pengajar', 'Pegawai Kantor'],
                            ['ustadz.fatkur@nuruliman.net', 'ustadzstaff123', 'Dua Fungsi', 'Staff Merangkap Guru'],
                            ['pimpinan.kiai@nuruliman.net', 'kiai123', 'Pimpinan', 'Kiai / Monitoring'],
                        ] as $preset)
                            <button type="button" onclick="fillAndSubmit('{{ $preset[0] }}', '{{ $preset[1] }}')" class="flex flex-col items-start p-2.5 rounded-xl border border-slate-100 bg-slate-50 text-left transition-all hover:border-brand-navy hover:bg-brand-sky/20">
                                <span class="text-[11px] font-bold text-brand-navy block">{{ $preset[2] }}</span>
                                <span class="text-[9px] text-slate-400">{{ $preset[3] }}</span>
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </main>

    <footer class="w-full py-4 text-center border-t border-slate-100 bg-white/40">
        <p class="text-[10px] font-medium text-slate-400 uppercase tracking-wider">&copy; {{ date('Y') }} {{ $branding['pondok_name'] }}. Seluruh Hak Cipta Dilindungi.</p>
    </footer>

    <script>
        function fillAndSubmit(email, password) {
            document.getElementById('email').value = email;
            document.getElementById('password').value = password;
            document.getElementById('loginForm').submit();
        }
    </script>
</body>
</html>
