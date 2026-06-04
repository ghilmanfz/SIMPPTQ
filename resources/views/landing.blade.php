<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PPTQ Nurul Iman - Sistem Informasi Manajemen</title>
    <!-- Tailwind CSS & Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <!-- Icons -->
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet" />
    <!-- Alpine JS -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="font-sans antialiased bg-slate-50 text-slate-800" x-data="landingPageData()">

    <!-- Top Header / Navigation -->
    <header class="sticky top-0 z-50 w-full border-b border-slate-100 bg-white/80 backdrop-blur-md">
        <div class="mx-auto flex max-w-7xl items-center justify-between px-6 py-4">
            <!-- Brand Logo -->
            <a href="/" class="flex items-center gap-3 group">
                <!-- Text logo -->
                <div x-show="logoType === 'text'" class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-brand-navy to-brand-navy-dark text-white font-bold text-lg shadow-md shadow-brand-navy/10 transition-transform group-hover:scale-105" x-text="logoText"></div>
                <!-- Image logo -->
                <div x-show="logoType === 'image'" class="h-10 w-10 rounded-xl overflow-hidden flex items-center justify-center bg-slate-100 border shadow-sm">
                    <img :src="logoImage || '/favicon.ico'" class="h-full w-full object-cover">
                </div>
                <div>
                    <span class="text-xl font-bold tracking-tight text-brand-navy block leading-none" x-text="pondokName"></span>
                    <span class="text-[10px] font-medium uppercase tracking-wider text-brand-green" x-text="pondokTagline"></span>
                </div>
            </a>

            <!-- Desktop Nav Links -->
            <nav class="hidden md:flex items-center gap-8">
                <a href="#profil" class="text-sm font-semibold text-slate-600 transition-colors hover:text-brand-navy">Profil</a>
                <a href="#fitur" class="text-sm font-semibold text-slate-600 transition-colors hover:text-brand-navy">Fitur Utama</a>
                <a href="#pengumuman" class="text-sm font-semibold text-slate-600 transition-colors hover:text-brand-navy">Pengumuman</a>
                <a href="#kontak" class="text-sm font-semibold text-slate-600 transition-colors hover:text-brand-navy">Kontak</a>
            </nav>

            <!-- Login Call to Action -->
            <div class="flex items-center gap-4">
                <a href="/login" class="inline-flex items-center justify-center gap-2 rounded-xl bg-brand-navy px-5 py-2.5 text-sm font-bold text-white shadow-lg shadow-brand-navy/20 transition-all hover:bg-brand-navy-dark hover:shadow-brand-navy/35 focus:outline-none focus:ring-2 focus:ring-brand-navy focus:ring-offset-2">
                    Masuk Portal
                    <i class="ri-arrow-right-line"></i>
                </a>
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="relative overflow-hidden bg-gradient-to-br from-brand-navy via-[#0c266f] to-brand-navy-dark pb-24 pt-20 text-white">
        <!-- Floating Accent Gradients -->
        <div class="absolute -top-40 -right-40 h-[600px] w-[600px] rounded-full bg-brand-green/10 blur-[120px] pointer-events-none"></div>
        <div class="absolute -bottom-20 -left-20 h-[400px] w-[400px] rounded-full bg-brand-teal/15 blur-[100px] pointer-events-none"></div>

        <div class="mx-auto max-w-7xl px-6 relative z-10">
            <div class="grid gap-12 lg:grid-cols-12 lg:items-center">
                <!-- Hero Left Text -->
                <div class="space-y-8 lg:col-span-7">
                    <div class="inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/5 px-4 py-1.5 backdrop-blur-sm">
                        <span class="flex h-2 w-2 rounded-full bg-brand-green animate-pulse"></span>
                        <span class="text-xs font-semibold tracking-wide text-brand-sky">Portal Sistem Informasi v1.0.0</span>
                    </div>

                    <h1 class="text-4xl font-extrabold tracking-tight sm:text-5xl lg:text-6xl leading-[1.1] text-white">
                        <span x-text="landingTitle"></span> <br/>
                        <span class="bg-gradient-to-r from-brand-sky via-teal-200 to-brand-green bg-clip-text text-transparent" x-text="landingTitleHighlight"></span>
                    </h1>

                    <p class="text-lg text-slate-300 max-w-xl leading-relaxed" x-text="landingDesc"></p>

                    <div class="flex flex-wrap gap-4 pt-2">
                        <a href="/login" class="inline-flex items-center justify-center gap-2 rounded-xl bg-brand-green px-6 py-3.5 text-base font-bold text-white shadow-lg shadow-brand-green/20 transition-all hover:bg-brand-green-dark hover:scale-[1.02]">
                            Buka Dashboard
                            <i class="ri-dashboard-3-line"></i>
                        </a>
                        <a href="#fitur" class="inline-flex items-center justify-center gap-2 rounded-xl border border-white/20 bg-white/5 px-6 py-3.5 text-base font-semibold text-white backdrop-blur-sm transition-all hover:bg-white/15">
                            Pelajari Selengkapnya
                        </a>
                    </div>
                </div>

                <!-- Hero Right Card Graphic or Custom Image -->
                <div class="lg:col-span-5 relative flex justify-center">
                    <!-- Default Glassmorphism Card Frame -->
                    <div x-show="landingHeroImage === 'default'" class="w-full max-w-[400px] rounded-3xl border border-white/20 bg-white/10 p-6 shadow-2xl backdrop-blur-md relative">
                        <div class="absolute -top-3 -left-3 flex h-8 w-8 items-center justify-center rounded-xl bg-brand-green text-white shadow-md">
                            <i class="ri-double-quotes-l"></i>
                        </div>
                        <div class="flex items-center gap-4 border-b border-white/10 pb-4 mb-6">
                            <div class="h-12 w-12 rounded-2xl bg-white/15 flex items-center justify-center text-brand-green text-2xl">
                                <i class="ri-shield-user-line"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-white leading-tight" x-text="pondokName"></h4>
                                <p class="text-xs text-brand-sky/70" x-text="pondokTagline"></p>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <div class="flex items-center justify-between rounded-xl bg-white/5 p-3.5 border border-white/5">
                                <div class="flex items-center gap-3">
                                    <i class="ri-user-heart-line text-brand-teal text-lg"></i>
                                    <span class="text-sm font-semibold text-slate-200">Personil Internal</span>
                                </div>
                                <span class="rounded-full bg-brand-teal/20 px-3 py-0.5 text-xs font-bold text-brand-teal">SDM Aktif</span>
                            </div>
                            <div class="flex items-center justify-between rounded-xl bg-white/5 p-3.5 border border-white/5">
                                <div class="flex items-center gap-3">
                                    <i class="ri-group-line text-brand-green text-lg"></i>
                                    <span class="text-sm font-semibold text-slate-200">Santri Terdaftar</span>
                                </div>
                                <span class="rounded-full bg-brand-green/20 px-3 py-0.5 text-xs font-bold text-brand-green">Santri</span>
                            </div>
                            <div class="flex items-center justify-between rounded-xl bg-white/5 p-3.5 border border-white/5">
                                <div class="flex items-center gap-3">
                                    <i class="ri-map-pin-user-line text-amber-400 text-lg"></i>
                                    <span class="text-sm font-semibold text-slate-200">Kehadiran GPS</span>
                                </div>
                                <span class="rounded-full bg-amber-400/20 px-3 py-0.5 text-xs font-bold text-amber-400">Presensi</span>
                            </div>
                        </div>
                    </div>

                    <!-- Custom Hero Image Vektor generated by Gemini -->
                    <div x-show="landingHeroImage === 'custom'" class="w-full max-w-[440px] rounded-3xl border border-white/10 bg-white/5 p-3 shadow-2xl backdrop-blur-sm overflow-hidden flex items-center justify-center">
                        <img src="/pondok_hero_banner.png" class="rounded-2xl w-full object-cover shadow-inner hover:scale-[1.01] transition-transform">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Quick Statistics Segment (Deloitte/Mayapada Header Style Accent) -->
    <section class="relative z-20 -mt-10 mx-auto max-w-5xl px-6">
        <div class="grid grid-cols-2 gap-4 rounded-3xl bg-white p-6 shadow-xl border border-slate-100 md:grid-cols-4">
            <div class="text-center p-4 border-r border-slate-100 last:border-0">
                <span class="block text-3xl font-extrabold text-brand-navy" x-text="statsPersonnel"></span>
                <span class="text-xs font-semibold tracking-wider text-slate-500 uppercase">Ustadz & Staff</span>
            </div>
            <div class="text-center p-4 border-r border-slate-100 md:border-r last:border-0">
                <span class="block text-3xl font-extrabold text-brand-navy" x-text="statsSantri"></span>
                <span class="text-xs font-semibold tracking-wider text-slate-500 uppercase">Santri Binaan</span>
            </div>
            <div class="text-center p-4 border-r border-slate-100 last:border-0">
                <span class="block text-3xl font-extrabold text-brand-navy" x-text="statsHalaqah"></span>
                <span class="text-xs font-semibold tracking-wider text-slate-500 uppercase">Halaqah Kelas</span>
            </div>
            <div class="text-center p-4 last:border-0">
                <span class="block text-3xl font-extrabold text-brand-navy" x-text="statsAccuracy"></span>
                <span class="text-xs font-semibold tracking-wider text-slate-500 uppercase">Data Terpusat</span>
            </div>
        </div>
    </section>

    <!-- Profile Section -->
    <section id="profil" class="py-24">
        <div class="mx-auto max-w-7xl px-6">
            <div class="text-center max-w-3xl mx-auto space-y-4 mb-16">
                <h2 class="text-xs font-bold tracking-wider text-brand-green uppercase">Profil Pondok</h2>
                <h3 class="text-3xl font-bold tracking-tight text-brand-navy sm:text-4xl">PPTQ Nurul Iman</h3>
                <p class="text-base text-slate-500 leading-relaxed">
                    Pondok Pesantren Tahfidzul Qur'an (PPTQ) Nurul Iman berfokus pada melahirkan generasi penghafal Al-Qur'an yang tangguh, berilmu luas, berintegritas tinggi, dan responsif terhadap perkembangan zaman melalui optimalisasi teknologi.
                </p>
            </div>

            <div class="grid gap-8 md:grid-cols-3">
                <!-- Visi Card -->
                <div class="rounded-3xl border border-slate-100 bg-white p-8 shadow-sm transition-all hover:shadow-md">
                    <div class="mb-5 flex h-12 w-12 items-center justify-center rounded-2xl bg-brand-sky text-brand-navy text-2xl">
                        <i class="ri-focus-3-line"></i>
                    </div>
                    <h4 class="text-lg font-bold text-brand-navy mb-3">Visi Utama</h4>
                    <p class="text-sm text-slate-500 leading-relaxed">
                        Menjadi institusi pencetak penghafal Al-Qur'an terkemuka yang unggul dalam akhlak, ilmu syar'i, sains dasar, dan tata kelola digital.
                    </p>
                </div>
                <!-- Misi Card -->
                <div class="rounded-3xl border border-slate-100 bg-white p-8 shadow-sm transition-all hover:shadow-md">
                    <div class="mb-5 flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-50 text-brand-green text-2xl">
                        <i class="ri-compass-3-line"></i>
                    </div>
                    <h4 class="text-lg font-bold text-brand-navy mb-3">Misi Unggulan</h4>
                    <p class="text-sm text-slate-500 leading-relaxed">
                        Menyelenggarakan pembelajaran Al-Qur'an bersanad, mengembangkan kurikulum terpadu berbasis karakter, dan menerapkan manajemen operasional modern.
                    </p>
                </div>
                <!-- Nilai Card -->
                <div class="rounded-3xl border border-slate-100 bg-white p-8 shadow-sm transition-all hover:shadow-md">
                    <div class="mb-5 flex h-12 w-12 items-center justify-center rounded-2xl bg-teal-50 text-brand-teal text-2xl">
                        <i class="ri-hearts-line"></i>
                    </div>
                    <h4 class="text-lg font-bold text-brand-navy mb-3">Nilai Pokok</h4>
                    <p class="text-sm text-slate-500 leading-relaxed">
                        Kejujuran (Ikhlas), Kedisiplinan (Istiqomah), Ukhuwah Islamiyah, dan Adaptabilitas terhadap keilmuan dan metodologi modern.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Key Features Section -->
    <section id="fitur" class="py-24 bg-slate-100/50 border-y border-slate-200/50">
        <div class="mx-auto max-w-7xl px-6">
            <div class="text-center max-w-3xl mx-auto space-y-4 mb-20">
                <h2 class="text-xs font-bold tracking-wider text-brand-green uppercase">Fitur Aplikasi</h2>
                <h3 class="text-3xl font-bold tracking-tight text-brand-navy sm:text-4xl">Modul Sistem Terintegrasi</h3>
                <p class="text-base text-slate-500 leading-relaxed">
                    Sistem dirancang untuk menyelesaikan masalah pencatatan manual, mencegah blunder sinkronisasi, dan memudahkan pimpinan memantau kondisi pondok secara transparan.
                </p>
            </div>

            <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
                <!-- Feature 1 -->
                <div class="flex gap-4 p-6 rounded-2xl bg-white border border-slate-100 shadow-sm transition-all hover:-translate-y-1">
                    <div class="flex-shrink-0 flex h-12 w-12 items-center justify-center rounded-xl bg-brand-sky text-brand-navy text-2xl">
                        <i class="ri-user-settings-line"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-brand-navy mb-1.5">Role & Permission</h4>
                        <p class="text-xs text-slate-500 leading-relaxed">Hak akses detail (Super Admin, Admin, Personil, Pimpinan) terikat modul dan hak aksi secara teratur.</p>
                    </div>
                </div>
                <!-- Feature 2 -->
                <div class="flex gap-4 p-6 rounded-2xl bg-white border border-slate-100 shadow-sm transition-all hover:-translate-y-1">
                    <div class="flex-shrink-0 flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-50 text-brand-green text-2xl">
                        <i class="ri-map-pin-2-line"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-brand-navy mb-1.5">GPS Presence Validated</h4>
                        <p class="text-xs text-slate-500 leading-relaxed">Personil check-in & check-out presensi harian tervalidasi radius koordinat GPS terdaftar.</p>
                    </div>
                </div>
                <!-- Feature 3 -->
                <div class="flex gap-4 p-6 rounded-2xl bg-white border border-slate-100 shadow-sm transition-all hover:-translate-y-1">
                    <div class="flex-shrink-0 flex h-12 w-12 items-center justify-center rounded-xl bg-teal-50 text-brand-teal text-2xl">
                        <i class="ri-qr-code-line"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-brand-navy mb-1.5">QR Card Scan Santri</h4>
                        <p class="text-xs text-slate-500 leading-relaxed">Presensi santri praktis menggunakan scan QR/barcode kartu santri fisik oleh petugas.</p>
                    </div>
                </div>
                <!-- Feature 4 -->
                <div class="flex gap-4 p-6 rounded-2xl bg-white border border-slate-100 shadow-sm transition-all hover:-translate-y-1">
                    <div class="flex-shrink-0 flex h-12 w-12 items-center justify-center rounded-xl bg-amber-50 text-amber-600 text-2xl">
                        <i class="ri-calendar-todo-line"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-brand-navy mb-1.5">Schedule & Swap Class</h4>
                        <p class="text-xs text-slate-500 leading-relaxed">Pengelolaan jadwal mengajar serta sistem approval tukar jam sementara pengajar yang aman.</p>
                    </div>
                </div>
                <!-- Feature 5 -->
                <div class="flex gap-4 p-6 rounded-2xl bg-white border border-slate-100 shadow-sm transition-all hover:-translate-y-1">
                    <div class="flex-shrink-0 flex h-12 w-12 items-center justify-center rounded-xl bg-purple-50 text-purple-600 text-2xl">
                        <i class="ri-bank-card-line"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-brand-navy mb-1.5">Sleek Payroll Slip</h4>
                        <p class="text-xs text-slate-500 leading-relaxed">Penghitungan gaji otomatis per periode berdasarkan status kerja, kehadiran, dan slip digital.</p>
                    </div>
                </div>
                <!-- Feature 6 -->
                <div class="flex gap-4 p-6 rounded-2xl bg-white border border-slate-100 shadow-sm transition-all hover:-translate-y-1">
                    <div class="flex-shrink-0 flex h-12 w-12 items-center justify-center rounded-xl bg-rose-50 text-rose-600 text-2xl">
                        <i class="ri-presentation-line"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-brand-navy mb-1.5">Laporan Strategis</h4>
                        <p class="text-xs text-slate-500 leading-relaxed">Dashboard pantau & rekap data perilaku, kunjungan wali, nilai santri untuk pimpinan.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Public Announcements Section -->
    <section id="pengumuman" class="py-24">
        <div class="mx-auto max-w-7xl px-6">
            <div class="flex flex-col md:flex-row md:items-end justify-between mb-16 gap-4">
                <div class="space-y-4 max-w-2xl">
                    <h2 class="text-xs font-bold tracking-wider text-brand-green uppercase">Papan Informasi</h2>
                    <h3 class="text-3xl font-bold tracking-tight text-brand-navy sm:text-4xl">Pengumuman & Agenda Terbaru</h3>
                    <p class="text-base text-slate-500">
                        Ikuti perkembangan kabar terkini dan jadwal kegiatan penting di PPTQ Nurul Iman.
                    </p>
                </div>
                <div>
                    <a href="/login" class="inline-flex items-center gap-2 text-sm font-bold text-brand-green hover:text-brand-green-dark">
                        Semua Pengumuman
                        <i class="ri-arrow-right-up-line"></i>
                    </a>
                </div>
            </div>

            <div class="grid gap-8 md:grid-cols-2 lg:grid-cols-3">
                <!-- Announcement Card 1 -->
                <article class="flex flex-col rounded-3xl bg-white border border-slate-100 overflow-hidden shadow-sm hover:shadow-md transition-shadow">
                    <div class="bg-gradient-to-r from-brand-navy to-brand-navy-dark px-6 py-4 flex items-center justify-between text-white">
                        <span class="text-[10px] font-bold uppercase tracking-wider bg-brand-green px-2.5 py-0.5 rounded-full">Umum</span>
                        <span class="text-xs font-medium text-slate-300">04 Jun 2026</span>
                    </div>
                    <div class="p-6 flex-1 flex flex-col justify-between space-y-4">
                        <div class="space-y-2">
                            <h4 class="font-bold text-brand-navy text-lg leading-snug">Penerimaan Raport dan Kunjungan Wali Santri Juni 2026</h4>
                            <p class="text-xs text-slate-500 line-clamp-3">Diberitahukan kepada seluruh wali santri bahwa pembagian laporan perkembangan bulanan santri sekaligus kunjungan terjadwal akan dilaksanakan akhir pekan ini.</p>
                        </div>
                        <a href="/login" class="inline-flex items-center gap-1.5 text-xs font-bold text-brand-navy hover:underline">
                            Baca Selengkapnya
                            <i class="ri-arrow-right-line"></i>
                        </a>
                    </div>
                </article>

                <!-- Announcement Card 2 -->
                <article class="flex flex-col rounded-3xl bg-white border border-slate-100 overflow-hidden shadow-sm hover:shadow-md transition-shadow">
                    <div class="bg-gradient-to-r from-brand-navy to-brand-navy-dark px-6 py-4 flex items-center justify-between text-white">
                        <span class="text-[10px] font-bold uppercase tracking-wider bg-brand-teal px-2.5 py-0.5 rounded-full">Personil</span>
                        <span class="text-xs font-medium text-slate-300">01 Jun 2026</span>
                    </div>
                    <div class="p-6 flex-1 flex flex-col justify-between space-y-4">
                        <div class="space-y-2">
                            <h4 class="font-bold text-brand-navy text-lg leading-snug">Sosialisasi Penggunaan Presensi GPS & Scan Kartu Santri</h4>
                            <p class="text-xs text-slate-500 line-clamp-3">Seluruh ustadz/ustadzah dan staff diwajibkan mengikuti sosialisasi tata cara operasional absensi GPS web dan scan barcode santri pada hari Senin depan.</p>
                        </div>
                        <a href="/login" class="inline-flex items-center gap-1.5 text-xs font-bold text-brand-navy hover:underline">
                            Baca Selengkapnya
                            <i class="ri-arrow-right-line"></i>
                        </a>
                    </div>
                </article>

                <!-- Announcement Card 3 -->
                <article class="flex flex-col rounded-3xl bg-white border border-slate-100 overflow-hidden shadow-sm hover:shadow-md transition-shadow">
                    <div class="bg-gradient-to-r from-brand-navy to-brand-navy-dark px-6 py-4 flex items-center justify-between text-white">
                        <span class="text-[10px] font-bold uppercase tracking-wider bg-amber-500 px-2.5 py-0.5 rounded-full text-white">Kegiatan</span>
                        <span class="text-xs font-medium text-slate-300">25 Mei 2026</span>
                    </div>
                    <div class="p-6 flex-1 flex flex-col justify-between space-y-4">
                        <div class="space-y-2">
                            <h4 class="font-bold text-brand-navy text-lg leading-snug">Ujian Tasmi' Al-Qur'an 30 Juz Sekali Duduk</h4>
                            <p class="text-xs text-slate-500 line-clamp-3">Mohon doa restu untuk kelancaran ananda Hafizh yang akan menempuh ujian tasmi' Al-Qur'an sekali duduk. Acara disiarkan langsung melalui platform internal.</p>
                        </div>
                        <a href="/login" class="inline-flex items-center gap-1.5 text-xs font-bold text-brand-navy hover:underline">
                            Baca Selengkapnya
                            <i class="ri-arrow-right-line"></i>
                        </a>
                    </div>
                </article>
            </div>
        </div>
    </section>

    <!-- Footer Segment / Call center info mimicking user's Deloitte image -->
    <footer id="kontak" class="bg-slate-900 pt-20 pb-8 text-slate-400 border-t border-slate-800">
        <div class="mx-auto max-w-7xl px-6">
            <div class="grid gap-12 lg:grid-cols-12 pb-16 border-b border-slate-800">
                <!-- Branding column -->
                <div class="lg:col-span-5 space-y-6">
                    <a href="/" class="flex items-center gap-3">
                        <!-- Text logo -->
                        <div x-show="logoType === 'text'" class="flex h-10 w-10 items-center justify-center rounded-xl bg-brand-green text-white font-bold text-lg shadow-md" x-text="logoText"></div>
                        <!-- Image logo -->
                        <div x-show="logoType === 'image'" class="h-10 w-10 rounded-xl overflow-hidden flex items-center justify-center bg-slate-100 border">
                            <img :src="logoImage || '/favicon.ico'" class="h-full w-full object-cover">
                        </div>
                        <div>
                            <span class="text-xl font-bold tracking-tight text-white block leading-none" x-text="pondokName"></span>
                            <span class="text-[10px] font-medium uppercase tracking-wider text-brand-green" x-text="pondokTagline"></span>
                        </div>
                    </a>
                    <p class="text-sm leading-relaxed text-slate-400 max-w-md">
                        Pesantren modern berbasis nilai islami dan pengelolaan digital yang transparan serta kredibel. Terdepan melahirkan generasi berakhlak mulia.
                    </p>
                </div>

                <!-- Fast menu links -->
                <div class="sm:col-span-4 lg:col-span-3 space-y-4">
                    <h5 class="font-bold text-white text-sm uppercase tracking-wider">Navigasi Cepat</h5>
                    <ul class="space-y-2.5 text-sm">
                        <li><a href="#profil" class="hover:text-white transition-colors">Profil Pondok</a></li>
                        <li><a href="#fitur" class="hover:text-white transition-colors">Sistem Manajemen</a></li>
                        <li><a href="#pengumuman" class="hover:text-white transition-colors">Informasi Papan</a></li>
                        <li><a href="/login" class="hover:text-white transition-colors">Login Staf & Pimpinan</a></li>
                    </ul>
                </div>

                <!-- Contact Info -->
                <div class="sm:col-span-8 lg:col-span-4 space-y-4">
                    <h5 class="font-bold text-white text-sm uppercase tracking-wider">Sekretariat & Hubungan</h5>
                    <ul class="space-y-3 text-sm">
                        <li class="flex items-start gap-2.5">
                            <i class="ri-map-pin-2-line text-brand-green text-base mt-0.5"></i>
                            <span>Jl. Sadewa Saraswati No. 32, Lantai 3, Sleman, D.I. Yogyakarta</span>
                        </li>
                        <li class="flex items-center gap-2.5">
                            <i class="ri-mail-line text-brand-green text-base"></i>
                            <span>info@pptqnuruliman.sch.id</span>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Deloitte / Mayapada Aesthetic Call Center Ribbon at bottom -->
            <div class="mt-12 flex flex-col md:flex-row items-center justify-between gap-6">
                <!-- Contact info ribbon (Call center text) -->
                <div class="flex flex-wrap items-center gap-y-2 gap-x-6 text-sm font-bold text-white">
                    <div class="flex items-center gap-2">
                        <span class="text-slate-400 text-xs font-medium uppercase tracking-wider">Sekretariat:</span>
                        <a href="tel:150770" class="hover:text-brand-green transition-colors">150 770</a>
                    </div>
                    <div class="h-4 w-px bg-slate-800 hidden md:block"></div>
                    <div class="flex items-center gap-2">
                        <span class="text-slate-400 text-xs font-medium uppercase tracking-wider">Layanan 24/7:</span>
                        <a href="tel:150990" class="hover:text-brand-green transition-colors text-brand-green">150 990</a>
                    </div>
                </div>

                <!-- Copyright -->
                <p class="text-xs text-slate-500">
                    &copy; 2026 PPTQ Nurul Iman. Hak Cipta Dilindungi Undang-Undang. Design inspired by Mayapada-Deloitte clean grid.
                </p>
            </div>
        </div>
    </footer>

    <script>
        function landingPageData() {
            return {
                logoType: localStorage.getItem('simpptq_logo_type') || 'text',
                logoText: localStorage.getItem('simpptq_logo_text') || 'NI',
                logoImage: localStorage.getItem('simpptq_logo_image') || '',
                pondokName: localStorage.getItem('simpptq_pondok_name') || 'PPTQ Nurul Iman',
                pondokTagline: localStorage.getItem('simpptq_pondok_tagline') || 'Sistem Manajemen Terpadu',
                landingTitle: localStorage.getItem('simpptq_landing_hero_title') || 'Membangun Generasi',
                landingTitleHighlight: localStorage.getItem('simpptq_landing_hero_title_highlight') || 'Qur\'ani & Unggul',
                landingDesc: localStorage.getItem('simpptq_landing_hero_desc') || 'Selamat datang di Sistem Informasi Manajemen Terpusat PPTQ Nurul Iman. Solusi digital modern untuk mengelola data personil, kehadiran GPS, perkembangan santri, penggajian, dan operasional kepondokan secara real-time.',
                landingHeroImage: localStorage.getItem('simpptq_landing_hero_image') || 'default',
                statsPersonnel: localStorage.getItem('simpptq_landing_stats_personnel') || '40+',
                statsSantri: localStorage.getItem('simpptq_landing_stats_santri') || '350+',
                statsHalaqah: localStorage.getItem('simpptq_landing_stats_halaqah') || '15+',
                statsAccuracy: localStorage.getItem('simpptq_landing_stats_accuracy') || '100%'
            };
        }
    </script>
</body>
</html>
