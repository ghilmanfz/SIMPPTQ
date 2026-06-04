<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Informasi Manajemen PPTQ - Dashboard</title>
    <!-- Tailwind CSS & Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <!-- Icons -->
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet" />
    <!-- ChartJS for monitoring charts -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Alpine JS (loaded at the end for proper DOM binding) -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="font-sans antialiased bg-slate-50 text-slate-800 h-screen overflow-hidden" x-data="simpptqApp()">

    <!-- Main Wrapper -->
    <div class="h-full flex overflow-hidden">

        <!-- ========================================== -->
        <!-- SIDEBAR NAVIGATION                         -->
        <!-- ========================================== -->
        <aside class="w-64 bg-brand-navy text-white flex flex-col justify-between flex-shrink-0 relative z-30 shadow-xl shadow-brand-navy/30">
            <!-- Background gradient overlay matching design theme -->
            <div class="absolute inset-0 bg-gradient-to-b from-[#0e2a4a]/80 to-brand-navy-dark/95 pointer-events-none -z-10"></div>
            
            <div class="flex flex-col h-full overflow-y-auto">
                <!-- Branding Header -->
                <div class="px-6 py-5 border-b border-white/10 flex items-center gap-3">
                    <!-- Text logo -->
                    <div x-show="logoType === 'text'" class="flex h-9 w-9 items-center justify-center rounded-xl bg-brand-green text-white font-bold text-base shadow-sm" x-text="logoText"></div>
                    <!-- Image logo -->
                    <div x-show="logoType === 'image'" class="h-9 w-9 rounded-xl overflow-hidden flex items-center justify-center bg-white/15 border border-white/10">
                        <img :src="logoImage || '/favicon.ico'" class="h-full w-full object-cover">
                    </div>
                    <div>
                        <span class="text-sm font-bold tracking-tight block" x-text="pondokName"></span>
                        <span class="text-[9px] font-medium tracking-wider uppercase text-brand-sky/60" x-text="pondokTagline"></span>
                    </div>
                </div>

                <!-- Logged In User Profile Summary -->
                <div class="px-6 py-4 bg-white/5 border-b border-white/10 flex items-center gap-3">
                    <div class="h-10 w-10 rounded-full bg-brand-teal/20 border border-brand-teal flex items-center justify-center text-brand-teal font-extrabold text-sm">
                        <span x-text="currentUserInitials()"></span>
                    </div>
                    <div class="overflow-hidden">
                        <span class="text-xs font-bold block truncate" x-text="currentUser.name"></span>
                        <span class="text-[9px] font-medium tracking-wider uppercase text-brand-green bg-brand-green/20 border border-brand-green/30 px-1.5 py-0.5 rounded-full inline-block mt-0.5" x-text="getRoleLabel()"></span>
                    </div>
                </div>

                <!-- Navigation List -->
                <nav class="flex-1 px-4 py-4 space-y-1">
                    <!-- General Dashboards -->
                    <div class="text-[9px] font-extrabold text-slate-400 uppercase tracking-widest px-3 mb-2">Umum & Utama</div>
                    
                    <button @click="activeTab = 'dashboard'" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-bold transition-all hover:bg-white/10"
                        :class="activeTab === 'dashboard' ? 'bg-brand-green text-white shadow-md shadow-brand-green/20' : 'text-slate-300 hover:text-white'">
                        <i class="ri-dashboard-3-line text-sm"></i>
                        Dashboard
                    </button>

                    <button @click="activeTab = 'announcements'" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-bold transition-all hover:bg-white/10"
                        :class="activeTab === 'announcements' ? 'bg-brand-green text-white shadow-md shadow-brand-green/20' : 'text-slate-300 hover:text-white'">
                        <i class="ri-notification-3-line text-sm"></i>
                        Pengumuman
                        <span x-show="announcements.filter(a => a.active).length > 0" class="ml-auto flex h-2 w-2 rounded-full bg-red-400"></span>
                    </button>

                    <!-- Staff Operations Block (Shown if has permission) -->
                    <template x-if="hasAccess('personnel_view') || hasAccess('attendance_check') || hasAccess('leave_apply') || hasAccess('payroll_view')">
                        <div class="pt-4">
                            <div class="text-[9px] font-extrabold text-slate-400 uppercase tracking-widest px-3 mb-2">Operasional Personil</div>
                            
                            <button x-show="hasAccess('presence_gps')" @click="activeTab = 'presence-gps'" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-bold transition-all hover:bg-white/10"
                                :class="activeTab === 'presence-gps' ? 'bg-brand-green text-white shadow-md shadow-brand-green/20' : 'text-slate-300 hover:text-white'">
                                <i class="ri-map-pin-user-line text-sm"></i>
                                Presensi GPS
                            </button>

                            <button x-show="hasAccess('schedule_view')" @click="activeTab = 'schedule'" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-bold transition-all hover:bg-white/10"
                                :class="activeTab === 'schedule' ? 'bg-brand-green text-white shadow-md shadow-brand-green/20' : 'text-slate-300 hover:text-white'">
                                <i class="ri-calendar-event-line text-sm"></i>
                                Jadwal Mengajar
                            </button>

                            <button x-show="hasAccess('leave_apply')" @click="activeTab = 'leaves'" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-bold transition-all hover:bg-white/10"
                                :class="activeTab === 'leaves' ? 'bg-brand-green text-white shadow-md shadow-brand-green/20' : 'text-slate-300 hover:text-white'">
                                <i class="ri-file-text-line text-sm"></i>
                                Pengajuan Izin/Cuti
                                <span x-show="getPendingLeavesCount() > 0 && hasAccess('leave_approve')" class="ml-auto bg-amber-500 text-white font-extrabold text-[9px] px-1.5 py-0.5 rounded-full" x-text="getPendingLeavesCount()"></span>
                            </button>

                            <button x-show="hasAccess('swap_apply')" @click="activeTab = 'swaps'" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-bold transition-all hover:bg-white/10"
                                :class="activeTab === 'swaps' ? 'bg-brand-green text-white shadow-md shadow-brand-green/20' : 'text-slate-300 hover:text-white'">
                                <i class="ri-swap-line text-sm"></i>
                                Tukar Jam Mengajar
                                <span x-show="getPendingSwapsCount() > 0 && hasAccess('swap_approve')" class="ml-auto bg-amber-500 text-white font-extrabold text-[9px] px-1.5 py-0.5 rounded-full" x-text="getPendingSwapsCount()"></span>
                            </button>

                            <button x-show="hasAccess('payroll_view')" @click="activeTab = 'payroll'" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-bold transition-all hover:bg-white/10"
                                :class="activeTab === 'payroll' ? 'bg-brand-green text-white shadow-md shadow-brand-green/20' : 'text-slate-300 hover:text-white'">
                                <i class="ri-bank-card-line text-sm"></i>
                                Penggajian & Slip
                            </button>
                        </div>
                    </template>

                    <!-- Santri & Academic Block (Shown if has permission) -->
                    <template x-if="hasAccess('santri_view') || hasAccess('class_view') || hasAccess('santri_presence')">
                        <div class="pt-4">
                            <div class="text-[9px] font-extrabold text-slate-400 uppercase tracking-widest px-3 mb-2">Manajemen Santri</div>
                            
                            <button x-show="hasAccess('santri_view')" @click="activeTab = 'santri'" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-bold transition-all hover:bg-white/10"
                                :class="activeTab === 'santri' ? 'bg-brand-green text-white shadow-md shadow-brand-green/20' : 'text-slate-300 hover:text-white'">
                                <i class="ri-contacts-line text-sm"></i>
                                Data & Kartu Santri
                            </button>

                            <button x-show="hasAccess('santri_presence')" @click="activeTab = 'presence-santri'" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-bold transition-all hover:bg-white/10"
                                :class="activeTab === 'presence-santri' ? 'bg-brand-green text-white shadow-md shadow-brand-green/20' : 'text-slate-300 hover:text-white'">
                                <i class="ri-qr-code-line text-sm"></i>
                                Scan Absensi Santri
                            </button>

                            <button x-show="hasAccess('class_view')" @click="activeTab = 'classes'" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-bold transition-all hover:bg-white/10"
                                :class="activeTab === 'classes' ? 'bg-brand-green text-white shadow-md shadow-brand-green/20' : 'text-slate-300 hover:text-white'">
                                <i class="ri-door-open-line text-sm"></i>
                                Pengelompokan Kelas
                            </button>

                            <button x-show="hasAccess('behavior_log')" @click="activeTab = 'behaviors'" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-bold transition-all hover:bg-white/10"
                                :class="activeTab === 'behaviors' ? 'bg-brand-green text-white shadow-md shadow-brand-green/20' : 'text-slate-300 hover:text-white'">
                                <i class="ri-shield-check-line text-sm"></i>
                                Perilaku & Poin
                            </button>

                            <button x-show="hasAccess('grade_log')" @click="activeTab = 'grades'" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-bold transition-all hover:bg-white/10"
                                :class="activeTab === 'grades' ? 'bg-brand-green text-white shadow-md shadow-brand-green/20' : 'text-slate-300 hover:text-white'">
                                <i class="ri-book-read-line text-sm"></i>
                                Nilai & Perkembangan
                            </button>

                            <button x-show="hasAccess('visit_log')" @click="activeTab = 'visits'" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-bold transition-all hover:bg-white/10"
                                :class="activeTab === 'visits' ? 'bg-brand-green text-white shadow-md shadow-brand-green/20' : 'text-slate-300 hover:text-white'">
                                <i class="ri-group-line text-sm"></i>
                                Kunjungan Wali
                            </button>
                        </div>
                    </template>

                    <!-- Administration Block (Shown to admin or super admin) -->
                    <template x-if="hasAccess('personnel_manage') || hasAccess('user_manage') || hasAccess('role_manage')">
                        <div class="pt-4">
                            <div class="text-[9px] font-extrabold text-slate-400 uppercase tracking-widest px-3 mb-2">Administrasi Pondok</div>
                            
                            <button x-show="hasAccess('personnel_manage')" @click="activeTab = 'personnel-directory'" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-bold transition-all hover:bg-white/10"
                                :class="activeTab === 'personnel-directory' ? 'bg-brand-green text-white shadow-md shadow-brand-green/20' : 'text-slate-300 hover:text-white'">
                                <i class="ri-group-line text-sm"></i>
                                Manajemen Personil
                            </button>

                            <button x-show="hasAccess('user_manage')" @click="activeTab = 'users'" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-bold transition-all hover:bg-white/10"
                                :class="activeTab === 'users' ? 'bg-brand-green text-white shadow-md shadow-brand-green/20' : 'text-slate-300 hover:text-white'">
                                <i class="ri-user-settings-line text-sm"></i>
                                Manajemen Akun
                            </button>

                            <button x-show="hasAccess('role_manage')" @click="activeTab = 'roles'" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-bold transition-all hover:bg-white/10"
                                :class="activeTab === 'roles' ? 'bg-brand-green text-white shadow-md shadow-brand-green/20' : 'text-slate-300 hover:text-white'">
                                <i class="ri-git-repository-private-line text-sm"></i>
                                Peran & Hak Akses
                            </button>

                            <button x-show="hasAccess('user_manage')" @click="activeTab = 'whatsapp'" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-bold transition-all hover:bg-white/10"
                                :class="activeTab === 'whatsapp' ? 'bg-brand-green text-white shadow-md shadow-brand-green/20' : 'text-slate-300 hover:text-white'">
                                <i class="ri-whatsapp-line text-sm"></i>
                                Integrasi WhatsApp
                            </button>

                            <button x-show="hasAccess('user_manage')" @click="activeTab = 'branding'" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-bold transition-all hover:bg-white/10"
                                :class="activeTab === 'branding' ? 'bg-brand-green text-white shadow-md shadow-brand-green/20' : 'text-slate-300 hover:text-white'">
                                <i class="ri-palette-line text-sm"></i>
                                Pengaturan Branding
                            </button>
                        </div>
                    </template>

                    <!-- Reporting -->
                    <div class="pt-4">
                        <div class="text-[9px] font-extrabold text-slate-400 uppercase tracking-widest px-3 mb-2">Eksekutif & Laporan</div>
                        
                        <button x-show="hasAccess('reports_view')" @click="activeTab = 'reports'" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-bold transition-all hover:bg-white/10"
                            :class="activeTab === 'reports' ? 'bg-brand-green text-white shadow-md shadow-brand-green/20' : 'text-slate-300 hover:text-white'">
                            <i class="ri-bar-chart-box-line text-sm"></i>
                            Laporan Strategis
                        </button>
                    </div>
                </nav>

                <!-- Sidebar Footer Links -->
                <div class="p-4 border-t border-white/10 space-y-1">
                    <button @click="activeTab = 'profile'" class="w-full flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-semibold text-slate-300 hover:text-white hover:bg-white/5 transition-colors">
                        <i class="ri-user-line text-sm"></i>
                        Profil Saya
                    </button>
                    <a href="/login" class="w-full flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-semibold text-red-300 hover:text-red-100 hover:bg-white/5 transition-colors">
                        <i class="ri-logout-box-line text-sm"></i>
                        Keluar Portal
                    </a>
                </div>
            </div>
        </aside>

        <!-- ========================================== -->
        <!-- MAIN CONTENT AREA                          -->
        <!-- ========================================== -->
        <div class="flex-1 flex flex-col overflow-hidden">
            
            <!-- Top Navbar -->
            <header class="h-16 border-b border-slate-100 bg-white flex items-center justify-between px-6 flex-shrink-0 relative z-20">
                <div class="flex items-center gap-4">
                    <h2 class="text-sm font-bold text-brand-navy uppercase tracking-wider" x-text="getTabTitle()"></h2>
                    <div class="h-4 w-px bg-slate-200"></div>
                    <p class="text-[11px] text-slate-500 font-medium" x-text="'Pondok Pesantren Tahfidzul Qur\'an Nurul Iman | Tgl: ' + getFormattedDate()"></p>
                </div>

                <div class="flex items-center gap-4">
                    <!-- Notification Bell -->
                    <div class="relative">
                        <button class="h-9 w-9 rounded-xl border border-slate-100 flex items-center justify-center text-slate-500 hover:bg-slate-50 hover:text-brand-navy relative transition-colors">
                            <i class="ri-notification-3-line"></i>
                            <span class="absolute top-1.5 right-1.5 h-2 w-2 rounded-full bg-brand-green"></span>
                        </button>
                    </div>

                    <!-- Profile Dropdown Widget -->
                    <div class="flex items-center gap-3 border-l border-slate-100 pl-4">
                        <div class="text-right">
                            <span class="text-xs font-bold block text-slate-700" x-text="currentUser.name"></span>
                            <span class="text-[9px] text-slate-400 block tracking-wide" x-text="currentUser.email"></span>
                        </div>
                        <div class="h-9 w-9 rounded-full bg-brand-sky text-brand-navy font-bold text-xs flex items-center justify-center border border-brand-sky shadow-inner">
                            <span x-text="currentUserInitials()"></span>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Scrollable Content Frame -->
            <main class="flex-1 overflow-y-auto bg-slate-50/50 p-6 relative">
                
                <!-- Toast alert overlays -->
                <div class="fixed top-20 right-6 z-50 max-w-sm space-y-2 pointer-events-none" x-show="toast.visible" x-transition>
                    <div class="pointer-events-auto rounded-2xl p-4 shadow-xl border flex gap-3"
                         :class="toast.type === 'success' ? 'bg-emerald-50 border-emerald-200 text-emerald-800' : 'bg-rose-50 border-rose-200 text-rose-800'">
                        <div class="text-lg">
                            <i :class="toast.type === 'success' ? 'ri-checkbox-circle-fill text-brand-green' : 'ri-error-warning-fill text-rose-500'"></i>
                        </div>
                        <div>
                            <h4 class="text-xs font-bold" x-text="toast.type === 'success' ? 'Berhasil' : 'Peringatan'"></h4>
                            <p class="text-[11px] font-medium leading-relaxed mt-0.5" x-text="toast.message"></p>
                        </div>
                    </div>
                </div>

                <!-- ========================================== -->
                <!-- MODULE 1: LANDING/DASHBOARD ROUTER        -->
                <!-- ========================================== -->
                <div x-show="activeTab === 'dashboard'" class="space-y-6">
                    
                    <!-- Welcome Card (Mayapada/Deloitte Header Style Gradient Panel) -->
                    <div class="rounded-3xl bg-gradient-to-r from-brand-navy to-[#0c266f] p-6 text-white border border-white/5 relative overflow-hidden shadow-lg shadow-brand-navy/15">
                        <div class="absolute -top-24 -right-24 h-48 w-48 rounded-full bg-brand-green/20 blur-xl pointer-events-none"></div>
                        <div class="relative z-10 max-w-2xl space-y-2">
                            <span class="text-[9px] font-bold uppercase tracking-wider bg-brand-green border border-brand-green/30 px-2 py-0.5 rounded-full inline-block">Portal Sistem Informasi Manajemen</span>
                            <h3 class="text-2xl font-extrabold tracking-tight">Ahlan wa Sahlan, <span x-text="currentUser.name"></span>!</h3>
                            <p class="text-xs text-brand-sky/80 leading-relaxed">
                                Selamat bertugas. Akses Anda saat ini disesuaikan dengan peran kerja dan fungsi kerja Anda. Silakan navigasikan menu sidebar untuk mengelola data pesantren Nurul Iman secara terintegrasi.
                            </p>
                        </div>
                    </div>

                    <!-- Stat Cards Matrix -->
                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        <!-- Stat Card 1 -->
                        <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm flex items-center justify-between transition-all hover:shadow-md">
                            <div class="space-y-1">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Total Personil</span>
                                <h4 class="text-2xl font-extrabold text-brand-navy" x-text="personnel.length">0</h4>
                                <span class="text-[9px] font-semibold text-brand-green block"><i class="ri-user-star-line"></i> Staff & Guru</span>
                            </div>
                            <div class="h-12 w-12 rounded-xl bg-brand-sky text-brand-navy flex items-center justify-center text-xl">
                                <i class="ri-group-line"></i>
                            </div>
                        </div>

                        <!-- Stat Card 2 -->
                        <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm flex items-center justify-between transition-all hover:shadow-md">
                            <div class="space-y-1">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Santri Terdaftar</span>
                                <h4 class="text-2xl font-extrabold text-brand-navy" x-text="santri.length">0</h4>
                                <span class="text-[9px] font-semibold text-brand-green block" x-text="santri.filter(s=>s.status==='Aktif').length + ' Status Aktif'"></span>
                            </div>
                            <div class="h-12 w-12 rounded-xl bg-emerald-50 text-brand-green flex items-center justify-center text-xl">
                                <i class="ri-contacts-book-line"></i>
                            </div>
                        </div>

                        <!-- Stat Card 3 -->
                        <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm flex items-center justify-between transition-all hover:shadow-md">
                            <div class="space-y-1">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Total Kelas</span>
                                <h4 class="text-2xl font-extrabold text-brand-navy" x-text="classes.length">0</h4>
                                <span class="text-[9px] font-semibold text-brand-teal block"><i class="ri-building-line"></i> Rombongan Belajar</span>
                            </div>
                            <div class="h-12 w-12 rounded-xl bg-teal-50 text-brand-teal flex items-center justify-center text-xl">
                                <i class="ri-door-open-line"></i>
                            </div>
                        </div>

                        <!-- Stat Card 4 -->
                        <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm flex items-center justify-between transition-all hover:shadow-md">
                            <div class="space-y-1">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Presensi Hari Ini</span>
                                <h4 class="text-2xl font-extrabold text-brand-navy" x-text="presenceLogs.length">0</h4>
                                <span class="text-[9px] font-semibold text-slate-400 block" x-text="'Tepat Waktu: ' + presenceLogs.filter(p=>p.status==='Tepat Waktu').length"></span>
                            </div>
                            <div class="h-12 w-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl">
                                <i class="ri-calendar-check-line"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Role-specific quick links and actions -->
                    <div class="grid gap-6 lg:grid-cols-12">
                        
                        <!-- Left Main Panel (Role Dependent Widget) -->
                        <div class="lg:col-span-8 space-y-6">
                            
                            <!-- Admin/Super Admin Dashboard Widgets -->
                            <div x-show="currentRole === 'admin' || currentRole === 'superadmin'" class="space-y-6">
                                <!-- Quick Operations Row -->
                                <div class="rounded-3xl border border-slate-100 bg-white p-6 shadow-sm space-y-4">
                                    <h4 class="text-xs font-extrabold text-brand-navy uppercase tracking-wider"><i class="ri-cursor-line"></i> Jalan Pintas Operasional</h4>
                                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                                        <button @click="activeTab = 'presence-santri'" class="p-4 rounded-2xl bg-brand-sky/20 border border-slate-100 hover:border-brand-navy text-center transition-all">
                                            <i class="ri-qr-code-line text-2xl text-brand-navy block mb-1"></i>
                                            <span class="text-[10px] font-bold text-slate-700">Scan Santri</span>
                                        </button>
                                        <button @click="activeTab = 'leaves'" class="p-4 rounded-2xl bg-emerald-50 border border-slate-100 hover:border-brand-green text-center transition-all relative">
                                            <span x-show="getPendingLeavesCount() > 0" class="absolute top-2 right-2 h-4 w-4 bg-amber-500 rounded-full text-white text-[9px] font-bold flex items-center justify-center" x-text="getPendingLeavesCount()"></span>
                                            <i class="ri-file-shield-line text-2xl text-brand-green block mb-1"></i>
                                            <span class="text-[10px] font-bold text-slate-700">Approve Izin</span>
                                        </button>
                                        <button @click="activeTab = 'swaps'" class="p-4 rounded-2xl bg-teal-50 border border-slate-100 hover:border-brand-teal text-center transition-all relative">
                                            <span x-show="getPendingSwapsCount() > 0" class="absolute top-2 right-2 h-4 w-4 bg-amber-500 rounded-full text-white text-[9px] font-bold flex items-center justify-center" x-text="getPendingSwapsCount()"></span>
                                            <i class="ri-swap-box-line text-2xl text-brand-teal block mb-1"></i>
                                            <span class="text-[10px] font-bold text-slate-700">Approve Jadwal</span>
                                        </button>
                                        <button @click="activeTab = 'payroll'" class="p-4 rounded-2xl bg-amber-50 border border-slate-100 hover:border-amber-600 text-center transition-all">
                                            <i class="ri-bank-card-line text-2xl text-amber-600 block mb-1"></i>
                                            <span class="text-[10px] font-bold text-slate-700">Proses Gaji</span>
                                        </button>
                                    </div>
                                </div>

                                <!-- Recent Pending Leave Requests (Admin Approval Board) -->
                                <div class="rounded-3xl border border-slate-100 bg-white p-6 shadow-sm space-y-4">
                                    <div class="flex justify-between items-center">
                                        <h4 class="text-xs font-extrabold text-brand-navy uppercase tracking-wider"><i class="ri-user-shared-line"></i> Persetujuan Izin / Cuti Karyawan</h4>
                                        <span class="text-[10px] font-bold text-slate-400" x-text="'Menunggu: ' + getPendingLeavesCount()"></span>
                                    </div>
                                    
                                    <div class="overflow-x-auto">
                                        <table class="w-full text-left text-xs text-slate-600">
                                            <thead>
                                                <tr class="border-b border-slate-100 text-slate-400 font-bold uppercase tracking-wider text-[10px]">
                                                    <th class="pb-3">Nama Karyawan</th>
                                                    <th class="pb-3">Jenis Izin</th>
                                                    <th class="pb-3">Periode Tanggal</th>
                                                    <th class="pb-3">Status</th>
                                                    <th class="pb-3 text-right">Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-slate-100 font-medium">
                                                <template x-for="req in leaveRequests" :key="req.id">
                                                    <tr>
                                                        <td class="py-3 font-bold text-brand-navy" x-text="req.name"></td>
                                                        <td class="py-3" x-text="req.type"></td>
                                                        <td class="py-3" x-text="req.start + ' s/d ' + req.end"></td>
                                                        <td class="py-3">
                                                            <span class="px-2 py-0.5 rounded-full text-[9px] font-bold"
                                                                :class="req.status === 'Disetujui' ? 'bg-emerald-50 text-brand-green border border-brand-green/20' : (req.status === 'Ditolak' ? 'bg-rose-50 text-rose-600 border border-rose-200' : 'bg-amber-50 text-amber-600 border border-amber-200') "
                                                                x-text="req.status">
                                                            </span>
                                                        </td>
                                                        <td class="py-3 text-right space-x-1">
                                                            <template x-if="req.status === 'Diajukan'">
                                                                <div>
                                                                    <button @click="approveLeave(req.id)" class="bg-brand-green text-white px-2.5 py-1 rounded-lg text-[10px] font-bold shadow-sm shadow-brand-green/10 hover:bg-brand-green-dark">Setujui</button>
                                                                    <button @click="rejectLeave(req.id)" class="bg-rose-500 text-white px-2.5 py-1 rounded-lg text-[10px] font-bold shadow-sm shadow-rose-500/10 hover:bg-rose-600">Tolak</button>
                                                                </div>
                                                            </template>
                                                            <template x-if="req.status !== 'Diajukan'">
                                                                <span class="text-[10px] text-slate-400 italic">Terkunci</span>
                                                            </template>
                                                        </td>
                                                    </tr>
                                                </template>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <!-- Teacher / Staff Dashboard Widget -->
                            <div x-show="currentRole === 'teacher' || currentRole === 'staff' || currentRole === 'hybrid'" class="space-y-6">
                                <!-- Self GPS Presence Panel (Simulation Module) -->
                                <div class="rounded-3xl border border-slate-100 bg-white p-6 shadow-sm space-y-6">
                                    <div class="flex justify-between items-center">
                                        <h4 class="text-xs font-extrabold text-brand-navy uppercase tracking-wider"><i class="ri-map-pin-line text-brand-green"></i> Validasi Presensi GPS Karyawan</h4>
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-bold text-brand-green">
                                            <span class="h-1.5 w-1.5 rounded-full bg-brand-green animate-pulse"></span>
                                            GPS GPS OK
                                        </span>
                                    </div>

                                    <div class="grid md:grid-cols-12 gap-6 items-center">
                                        <!-- Mock Map visualization -->
                                        <div class="md:col-span-5 border border-slate-100 rounded-2xl bg-brand-sky/20 overflow-hidden relative flex flex-col justify-between p-4 h-48">
                                            <div class="absolute inset-0 bg-[radial-gradient(circle_at_center,_var(--tw-gradient-stops))] from-white/80 via-transparent to-transparent pointer-events-none"></div>
                                            <!-- Concentric radius lines representing radius validation -->
                                            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 rounded-full border border-dashed border-brand-green/30 h-28 w-28 flex items-center justify-center">
                                                <div class="rounded-full border border-dashed border-brand-green/50 h-16 w-16 flex items-center justify-center bg-brand-green/5">
                                                    <!-- Location Pin -->
                                                    <i class="ri-map-pin-user-fill text-brand-navy text-2xl animate-bounce"></i>
                                                </div>
                                            </div>
                                            <div class="text-[9px] font-bold text-slate-500 z-10">Titik Koordinat: -7.782, 110.372</div>
                                            <div class="text-[9px] font-bold text-slate-500 z-10 text-right">Radius Valid: 100m</div>
                                        </div>

                                        <!-- Attendance Actions -->
                                        <div class="md:col-span-7 space-y-4">
                                            <div class="space-y-1.5">
                                                <h5 class="text-xs font-bold text-slate-600">Simulasikan Posisi Anda saat ini:</h5>
                                                <div class="grid grid-cols-2 gap-2">
                                                    <button @click="gpsInsideRadius = true; triggerToast('success', 'Simulasi diubah: Anda kini di dalam koordinat valid (Radius 45 meter dari Masjid).')" 
                                                        class="px-3 py-2 rounded-xl text-[10px] font-bold border transition-all"
                                                        :class="gpsInsideRadius ? 'bg-brand-navy text-white border-brand-navy' : 'bg-slate-50 text-slate-600 border-slate-200'">
                                                        Di Dalam Radius
                                                    </button>
                                                    <button @click="gpsInsideRadius = false; triggerToast('warning', 'Simulasi diubah: Anda berada di luar koordinat valid (Radius 1200 meter dari Kantor).')"
                                                        class="px-3 py-2 rounded-xl text-[10px] font-bold border transition-all"
                                                        :class="!gpsInsideRadius ? 'bg-brand-navy text-white border-brand-navy' : 'bg-slate-50 text-slate-600 border-slate-200'">
                                                        Di Luar Radius
                                                    </button>
                                                </div>
                                            </div>

                                            <div class="border-t border-slate-100 pt-4 flex gap-3">
                                                <button @click="performCheckIn()" class="flex-1 rounded-xl bg-brand-green py-3 text-xs font-bold text-white shadow-md shadow-brand-green/10 hover:bg-brand-green-dark transition-all">
                                                    <i class="ri-login-box-line"></i> Check In
                                                </button>
                                                <button @click="performCheckOut()" class="flex-1 rounded-xl bg-brand-navy py-3 text-xs font-bold text-white shadow-md shadow-brand-navy/10 hover:bg-brand-navy-dark transition-all">
                                                    <i class="ri-logout-box-line"></i> Check Out
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Class Swap Request Panel for Teachers -->
                                <div x-show="currentRole === 'teacher' || currentRole === 'hybrid'" class="rounded-3xl border border-slate-100 bg-white p-6 shadow-sm space-y-4">
                                    <div class="flex justify-between items-center">
                                        <h4 class="text-xs font-extrabold text-brand-navy uppercase tracking-wider"><i class="ri-swap-line"></i> Ajukan Tukar Jam Mengajar</h4>
                                        <button @click="activeTab = 'swaps'" class="text-[10px] font-bold text-brand-green hover:underline">Semua Pengajuan</button>
                                    </div>
                                    <p class="text-[11px] text-slate-400">Pengajar dapat mengajukan pemindahan sesi mengajar ke tanggal tertentu kepada asatidzah lainnya yang bersangkutan.</p>
                                    <button @click="activeTab = 'swaps'" class="inline-flex items-center gap-1.5 text-xs font-bold text-brand-navy border border-slate-200 px-4 py-2 rounded-xl hover:bg-slate-50">
                                        <i class="ri-add-line"></i>
                                        Isi Formulir Pengajuan
                                    </button>
                                </div>
                            </div>

                            <!-- Pimpinan Pondok (Executive Monitoring Mode) Dashboard Widgets -->
                            <div x-show="currentRole === 'leader'" class="space-y-6">
                                <!-- Interactive Charts segment -->
                                <div class="rounded-3xl border border-slate-100 bg-white p-6 shadow-sm space-y-4">
                                    <h4 class="text-xs font-extrabold text-brand-navy uppercase tracking-wider"><i class="ri-bar-chart-box-line text-brand-green"></i> Analitik Tingkat Kehadiran Harian Personil</h4>
                                    <!-- Simple Canvas for Chart.js -->
                                    <div class="h-60 relative">
                                        <canvas id="leaderDashboardChart" class="w-full h-full"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Right Sidebar Widget (Universal Panel) -->
                        <div class="lg:col-span-4 space-y-6">
                            
                            <!-- Announcements feed Widget -->
                            <div class="rounded-3xl border border-slate-100 bg-white p-6 shadow-sm space-y-4">
                                <div class="flex justify-between items-center">
                                    <h4 class="text-xs font-extrabold text-brand-navy uppercase tracking-wider"><i class="ri-notification-badge-line text-amber-500"></i> Pengumuman Terbaru</h4>
                                    <button @click="activeTab = 'announcements'" class="text-[10px] font-bold text-brand-green hover:underline">Semua</button>
                                </div>
                                <div class="space-y-3.5 max-h-[380px] overflow-y-auto pr-1">
                                    <template x-for="ann in announcements" :key="ann.id">
                                        <div class="p-3.5 rounded-xl border border-slate-100 bg-slate-50/50 space-y-2 hover:border-brand-navy transition-all" x-show="ann.active">
                                            <div class="flex items-center justify-between text-[9px] font-bold text-slate-400">
                                                <span class="rounded bg-brand-navy text-white px-1.5 py-0.5 uppercase tracking-wider" x-text="ann.target"></span>
                                                <span x-text="ann.date"></span>
                                            </div>
                                            <h5 class="text-xs font-bold text-brand-navy" x-text="ann.title"></h5>
                                            <p class="text-[10px] text-slate-500 leading-relaxed" x-text="ann.content"></p>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ========================================== -->
                <!-- MODULE 2: USER MANAGEMENT                  -->
                <!-- ========================================== -->
                <div x-show="activeTab === 'users'" class="space-y-6">
                    <div class="rounded-3xl border border-slate-100 bg-white p-6 shadow-sm space-y-6">
                        <div class="flex justify-between items-center">
                            <div>
                                <h3 class="text-base font-extrabold text-brand-navy tracking-tight">Daftar Akun Login</h3>
                                <p class="text-xs text-slate-400">Setiap data personil dihubungkan dengan maksimal satu akun login sistem.</p>
                            </div>
                            <button @click="openCreateUserModal()" class="rounded-xl bg-brand-green text-white font-bold text-xs px-4 py-2.5 shadow-sm shadow-brand-green/20 hover:bg-brand-green-dark">
                                <i class="ri-add-line"></i> Akun Baru
                            </button>
                        </div>

                        <!-- Data table user accounts -->
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-xs text-slate-600">
                                <thead>
                                    <tr class="border-b border-slate-100 text-slate-400 font-bold uppercase tracking-wider text-[10px]">
                                        <th class="pb-3">Username / Email</th>
                                        <th class="pb-3">Personil Terhubung</th>
                                        <th class="pb-3">Peran Akses (Role)</th>
                                        <th class="pb-3">Status Akun</th>
                                        <th class="pb-3 text-right">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 font-medium">
                                    <template x-for="user in users" :key="user.id">
                                        <tr>
                                            <td class="py-3.5 font-bold text-brand-navy" x-text="user.email"></td>
                                            <td class="py-3.5" x-text="user.personnel_name"></td>
                                            <td class="py-3.5">
                                                <span class="rounded bg-brand-sky text-brand-navy font-bold text-[9px] px-2 py-0.5 uppercase tracking-wide border border-brand-sky" x-text="user.role"></span>
                                            </td>
                                            <td class="py-3.5">
                                                <span class="px-2 py-0.5 rounded-full text-[9px] font-bold"
                                                    :class="user.active ? 'bg-emerald-50 text-brand-green border border-brand-green/20' : 'bg-slate-50 text-slate-400 border border-slate-200'"
                                                    x-text="user.active ? 'Aktif' : 'Non-Aktif'">
                                                </span>
                                            </td>
                                            <td class="py-3.5 text-right space-x-1.5">
                                                <button @click="toggleUserActive(user.id)" class="text-[10px] font-bold text-slate-500 hover:text-brand-navy">Toggle Status</button>
                                                <button @click="resetUserPassword(user.id)" class="text-[10px] font-bold text-brand-green hover:underline">Reset Sandi</button>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- ========================================== -->
                <!-- MODULE 3: ROLES & PERMISSIONS             -->
                <!-- ========================================== -->
                <div x-show="activeTab === 'roles'" class="space-y-6">
                    <div class="grid gap-6 lg:grid-cols-12">
                        <!-- Role Picker -->
                        <div class="lg:col-span-4 rounded-3xl border border-slate-100 bg-white p-6 shadow-sm space-y-4">
                            <h4 class="text-xs font-extrabold text-brand-navy uppercase tracking-wider">Peran Akses</h4>
                            <div class="space-y-2">
                                <template x-for="r in roles" :key="r.name">
                                    <button @click="selectedRoleForEdit = r.name" 
                                        class="w-full flex justify-between items-center p-3 rounded-xl border text-left text-xs font-bold transition-all"
                                        :class="selectedRoleForEdit === r.name ? 'border-brand-navy bg-brand-sky/20 text-brand-navy' : 'border-slate-100 hover:bg-slate-50 text-slate-700'">
                                        <span x-text="r.label"></span>
                                        <i class="ri-arrow-right-s-line text-base text-slate-400"></i>
                                    </button>
                                </template>
                            </div>
                        </div>

                        <!-- Permissions Config Checkbox Grid -->
                        <div class="lg:col-span-8 rounded-3xl border border-slate-100 bg-white p-6 shadow-sm space-y-6">
                            <div>
                                <h4 class="text-xs font-extrabold text-brand-navy uppercase tracking-wider">
                                    Konfigurasi Hak Modul: <span class="text-brand-green" x-text="getRoleLabelOf(selectedRoleForEdit)"></span>
                                </h4>
                                <p class="text-[11px] text-slate-400 mt-1">Mengaktifkan centang di bawah ini langsung merubah hak menu dari menu navigasi dashboard demo.</p>
                            </div>

                            <div class="grid sm:grid-cols-2 gap-4">
                                <template x-for="perm in availablePermissions" :key="perm.key">
                                    <label class="flex items-start gap-3 p-3 rounded-xl border border-slate-100 hover:bg-slate-50/50 cursor-pointer">
                                        <input type="checkbox" 
                                            class="h-4.5 w-4.5 rounded border-slate-200 text-brand-green focus:ring-brand-green mt-0.5"
                                            :checked="hasRolePermission(selectedRoleForEdit, perm.key)"
                                            @change="toggleRolePermission(selectedRoleForEdit, perm.key)">
                                        <div>
                                            <span class="text-xs font-bold text-slate-700 block" x-text="perm.label"></span>
                                            <span class="text-[10px] text-slate-400" x-text="perm.desc"></span>
                                        </div>
                                    </label>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ========================================== -->
                <!-- MODULE 4: PERSONNEL DIRECTORY              -->
                <!-- ========================================== -->
                <div x-show="activeTab === 'personnel-directory'" class="space-y-6">
                    <div class="rounded-3xl border border-slate-100 bg-white p-6 shadow-sm space-y-6">
                        <div class="flex justify-between items-center">
                            <div>
                                <h3 class="text-base font-extrabold text-brand-navy tracking-tight font-sans">Database Personil Internal</h3>
                                <p class="text-xs text-slate-400">Pegawai kantor, asatidzah/guru, petugas penanggung jawab dan direktur.</p>
                            </div>
                            <button @click="openCreatePersonnelModal()" class="rounded-xl bg-brand-green text-white font-bold text-xs px-4 py-2.5 shadow-sm shadow-brand-green/20 hover:bg-brand-green-dark">
                                <i class="ri-user-add-line"></i> Tambah Karyawan
                            </button>
                        </div>

                        <!-- Filters -->
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 bg-slate-50 p-4 rounded-2xl border border-slate-100">
                            <div>
                                <label class="block text-[9px] font-bold text-slate-400 uppercase tracking-wide mb-1.5">Fungsi Kerja</label>
                                <select x-model="filters.personnel.fungsi" class="block w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs text-slate-600 focus:outline-none">
                                    <option value="Semua">Semua</option>
                                    <option value="Pengajar">Pengajar</option>
                                    <option value="Non-Pengajar">Non-Pengajar</option>
                                    <option value="Dua Fungsi">Dua Fungsi</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[9px] font-bold text-slate-400 uppercase tracking-wide mb-1.5">Status Kerja</label>
                                <select x-model="filters.personnel.status" class="block w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs text-slate-600 focus:outline-none">
                                    <option value="Semua">Semua</option>
                                    <option value="Tetap">Tetap</option>
                                    <option value="Tidak Tetap">Tidak Tetap (GTT/GTY)</option>
                                </select>
                            </div>
                        </div>

                        <!-- Data table personnel -->
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-xs text-slate-600">
                                <thead>
                                    <tr class="border-b border-slate-100 text-slate-400 font-bold uppercase tracking-wider text-[10px]">
                                        <th class="pb-3">Nama Lengkap / NIK</th>
                                        <th class="pb-3">Fungsi Kerja</th>
                                        <th class="pb-3">Status Hubungan</th>
                                        <th class="pb-3">Jabatan / Unit</th>
                                        <th class="pb-3">Dokumen Pendukung</th>
                                        <th class="pb-3 text-right">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 font-medium">
                                    <template x-for="p in getFilteredPersonnel()" :key="p.id">
                                        <tr>
                                            <td class="py-4">
                                                <span class="font-bold text-brand-navy block" x-text="p.name"></span>
                                                <span class="text-[10px] text-slate-400" x-text="'NIK: ' + p.nik"></span>
                                            </td>
                                            <td class="py-4">
                                                <span class="rounded bg-brand-sky text-brand-navy font-bold text-[9px] px-2 py-0.5" x-text="p.fungsi"></span>
                                            </td>
                                            <td class="py-4" x-text="p.status_kerja"></td>
                                            <td class="py-4">
                                                <span class="block" x-text="p.jabatan"></span>
                                                <span class="text-[9px] text-slate-400" x-text="p.email"></span>
                                            </td>
                                            <td class="py-4">
                                                <div class="flex flex-wrap gap-1">
                                                    <template x-for="doc in p.documents" :key="doc">
                                                        <span class="rounded-full bg-slate-100 border border-slate-200 text-slate-500 font-bold text-[9px] px-2 py-0.5 flex items-center gap-1">
                                                            <i class="ri-file-pdf-line text-[10px] text-red-500"></i>
                                                            <span x-text="doc"></span>
                                                        </span>
                                                    </template>
                                                </div>
                                            </td>
                                            <td class="py-4 text-right space-x-1">
                                                <button @click="openEditPersonnelModal(p)" class="text-[10px] font-bold text-brand-green hover:underline">Edit</button>
                                                <button @click="deletePersonnel(p.id)" class="text-[10px] font-bold text-rose-500 hover:text-rose-700">Hapus</button>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- ========================================== -->
                <!-- MODULE 5: JADWAL MENGAJAR                  -->
                <!-- ========================================== -->
                <div x-show="activeTab === 'schedule'" class="space-y-6">
                    <div class="rounded-3xl border border-slate-100 bg-white p-6 shadow-sm space-y-6">
                        <div class="flex justify-between items-center">
                            <div>
                                <h3 class="text-base font-extrabold text-brand-navy tracking-tight">Jadwal Mengajar Asatidzah</h3>
                                <p class="text-xs text-slate-400">Jadwal master mingguan pengajar halaqah / mapel santri PPTQ.</p>
                            </div>
                        </div>

                        <!-- Timetable grid -->
                        <div class="grid md:grid-cols-5 gap-4">
                            <template x-for="day in ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat']" :key="day">
                                <div class="rounded-2xl border border-slate-100 bg-slate-50 p-4 space-y-3">
                                    <h4 class="text-xs font-bold text-brand-navy border-b border-slate-200 pb-2 flex justify-between items-center">
                                        <span x-text="day"></span>
                                        <i class="ri-calendar-line text-slate-400"></i>
                                    </h4>
                                    
                                    <div class="space-y-2">
                                        <template x-for="sched in getSchedulesForDay(day)" :key="sched.id">
                                            <div class="p-3 rounded-xl border border-slate-200/60 bg-white space-y-1.5 shadow-sm">
                                                <div class="flex justify-between text-[9px] font-bold text-brand-green">
                                                    <span x-text="sched.time"></span>
                                                    <span class="rounded bg-brand-sky text-brand-navy px-1.5 py-0.2" x-text="sched.class_name"></span>
                                                </div>
                                                <h5 class="text-xs font-bold text-brand-navy" x-text="sched.subject"></h5>
                                                <p class="text-[10px] font-medium text-slate-500" x-text="'Ustadz: ' + sched.teacher_name"></p>
                                            </div>
                                        </template>
                                        <template x-if="getSchedulesForDay(day).length === 0">
                                            <p class="text-[10px] text-slate-400 italic text-center py-4">Kosong</p>
                                        </template>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- ========================================== -->
                <!-- MODULE 6: EMPLOYEE GPS PRESENCE LOGS       -->
                <!-- ========================================== -->
                <div x-show="activeTab === 'presence-gps'" class="space-y-6">
                    <div class="rounded-3xl border border-slate-100 bg-white p-6 shadow-sm space-y-6">
                        <div class="flex justify-between items-center">
                            <div>
                                <h3 class="text-base font-extrabold text-brand-navy tracking-tight">Riwayat Absensi Kehadiran GPS</h3>
                                <p class="text-xs text-slate-400">Daftar rekaman log check-in GPS web harian personil.</p>
                            </div>
                        </div>

                        <!-- Log Table -->
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-xs text-slate-600">
                                <thead>
                                    <tr class="border-b border-slate-100 text-slate-400 font-bold uppercase tracking-wider text-[10px]">
                                        <th class="pb-3">Nama Karyawan</th>
                                        <th class="pb-3">Tanggal Hari</th>
                                        <th class="pb-3">Jam Masuk</th>
                                        <th class="pb-3">Jam Pulang</th>
                                        <th class="pb-3">Titik Lokasi</th>
                                        <th class="pb-3">Verifikasi Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 font-medium">
                                    <template x-for="log in presenceLogs" :key="log.id">
                                        <tr>
                                            <td class="py-3.5 font-bold text-brand-navy" x-text="log.name"></td>
                                            <td class="py-3.5" x-text="log.date"></td>
                                            <td class="py-3.5 text-brand-green font-bold" x-text="log.check_in || '-'"></td>
                                            <td class="py-3.5 text-slate-500 font-bold" x-text="log.check_out || '-'"></td>
                                            <td class="py-3.5" x-text="log.location"></td>
                                            <td class="py-3.5">
                                                <span class="px-2.5 py-0.5 rounded-full text-[9px] font-bold"
                                                    :class="log.status === 'Tepat Waktu' ? 'bg-emerald-50 text-brand-green border border-brand-green/20' : (log.status === 'Terlambat' ? 'bg-amber-50 text-amber-600 border border-amber-200' : 'bg-red-50 text-red-600 border border-red-200') "
                                                    x-text="log.status">
                                                </span>
                                            </td>
                                        </tr>
                                    </template>
                                    <template x-if="presenceLogs.length === 0">
                                        <tr>
                                            <td colspan="6" class="py-8 text-center text-slate-400 italic">Belum ada absensi tercatat hari ini. Silakan simulasikan check-in di menu Dashboard.</td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- ========================================== -->
                <!-- MODULE 7: LEAVE REQUESTS                    -->
                <!-- ========================================== -->
                <div x-show="activeTab === 'leaves'" class="space-y-6">
                    <div class="grid gap-6 lg:grid-cols-12">
                        <!-- Application Form for Personnel -->
                        <div x-show="hasAccess('leave_apply')" class="lg:col-span-5 rounded-3xl border border-slate-100 bg-white p-6 shadow-sm space-y-4">
                            <h4 class="text-xs font-extrabold text-brand-navy uppercase tracking-wider">Form Pengajuan Izin / Cuti</h4>
                            
                            <form onsubmit="handleLeaveForm(event)" class="space-y-4">
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-2">Tipe Izin</label>
                                    <select id="leave_type" class="block w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-3 text-xs text-slate-700 focus:outline-none focus:ring-1 focus:ring-brand-navy">
                                        <option value="Sakit">Sakit (Dengan SKD)</option>
                                        <option value="Izin Mendesak">Izin Penting & Mendesak</option>
                                        <option value="Cuti Tahunan">Cuti Tahunan</option>
                                    </select>
                                </div>
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-2">Mulai Tanggal</label>
                                        <input type="date" id="leave_start" required class="block w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-xs text-slate-700 focus:outline-none">
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-2">Sampai Tanggal</label>
                                        <input type="date" id="leave_end" required class="block w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-xs text-slate-700 focus:outline-none">
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-2">Alasan & Dokumen</label>
                                    <textarea id="leave_reason" placeholder="Keterangan keperluan..." rows="3" required class="block w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-xs text-slate-700 focus:outline-none"></textarea>
                                </div>
                                <button type="submit" class="w-full rounded-xl bg-brand-navy py-3 text-xs font-bold text-white shadow-md hover:bg-brand-navy-dark">Kirim Pengajuan</button>
                            </form>
                        </div>

                        <!-- Status List -->
                        <div class="rounded-3xl border border-slate-100 bg-white p-6 shadow-sm space-y-4"
                             :class="hasAccess('leave_apply') ? 'lg:col-span-7' : 'lg:col-span-12'">
                            <h4 class="text-xs font-extrabold text-brand-navy uppercase tracking-wider">Riwayat Pengajuan & Persetujuan</h4>
                            
                            <div class="overflow-x-auto">
                                <table class="w-full text-left text-xs text-slate-600">
                                    <thead>
                                        <tr class="border-b border-slate-100 text-slate-400 font-bold uppercase tracking-wider text-[10px]">
                                            <th class="pb-3">Pengaju</th>
                                            <th class="pb-3">Kategori</th>
                                            <th class="pb-3">Durasi</th>
                                            <th class="pb-3">Keterangan</th>
                                            <th class="pb-3">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100 font-medium">
                                        <template x-for="req in leaveRequests" :key="req.id">
                                            <tr>
                                                <td class="py-3 font-bold text-brand-navy" x-text="req.name"></td>
                                                <td class="py-3" x-text="req.type"></td>
                                                <td class="py-3" x-text="req.start + ' s/d ' + req.end"></td>
                                                <td class="py-3 text-[10px]" x-text="req.reason"></td>
                                                <td class="py-3">
                                                    <span class="px-2 py-0.5 rounded-full text-[9px] font-bold"
                                                        :class="req.status === 'Disetujui' ? 'bg-emerald-50 text-brand-green border border-brand-green/20' : (req.status === 'Ditolak' ? 'bg-rose-50 text-rose-600 border border-rose-200' : 'bg-amber-50 text-amber-600 border border-amber-200') "
                                                        x-text="req.status">
                                                    </span>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ========================================== -->
                <!-- MODULE 8: CLASS SWAP REQUESTS              -->
                <!-- ========================================== -->
                <div x-show="activeTab === 'swaps'" class="space-y-6">
                    <div class="grid gap-6 lg:grid-cols-12">
                        <!-- Request Form -->
                        <div x-show="hasAccess('swap_apply')" class="lg:col-span-5 rounded-3xl border border-slate-100 bg-white p-6 shadow-sm space-y-4">
                            <h4 class="text-xs font-extrabold text-brand-navy uppercase tracking-wider">Form Tukar Jam Mengajar</h4>
                            
                            <form onsubmit="handleSwapForm(event)" class="space-y-4">
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-2">Pilih Jadwal Master Anda</label>
                                    <select id="swap_schedule" class="block w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-3 text-xs text-slate-700 focus:outline-none">
                                        <template x-for="sched in getMySchedules()" :key="sched.id">
                                            <option :value="sched.id" x-text="sched.day + ' (' + sched.time + ') - ' + sched.subject + ' [' + sched.class_name + ']'"></option>
                                        </template>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-2">Tanggal Berhalangan</label>
                                    <input type="date" id="swap_date" required class="block w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-xs text-slate-700 focus:outline-none">
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-2">Pilih Guru Pengganti / Substitusi</label>
                                    <select id="swap_target" class="block w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-3 text-xs text-slate-700 focus:outline-none">
                                        <template x-for="t in personnel.filter(p => p.fungsi === 'Pengajar' || p.fungsi === 'Dua Fungsi')" :key="t.id">
                                            <option :value="t.name" x-text="t.name"></option>
                                        </template>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-2">Alasan Berhalangan</label>
                                    <textarea id="swap_reason" placeholder="Alasan tukar jam..." rows="2" required class="block w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-xs text-slate-700 focus:outline-none"></textarea>
                                </div>
                                <button type="submit" class="w-full rounded-xl bg-brand-navy py-3 text-xs font-bold text-white shadow-md hover:bg-brand-navy-dark">Ajukan Tukar Jam</button>
                            </form>
                        </div>

                        <!-- Status List -->
                        <div class="rounded-3xl border border-slate-100 bg-white p-6 shadow-sm space-y-4"
                             :class="hasAccess('swap_apply') ? 'lg:col-span-7' : 'lg:col-span-12'">
                            <h4 class="text-xs font-extrabold text-brand-navy uppercase tracking-wider">Log Pertukaran Jam Mengajar</h4>
                            
                            <div class="overflow-x-auto">
                                <table class="w-full text-left text-xs text-slate-600">
                                    <thead>
                                        <tr class="border-b border-slate-100 text-slate-400 font-bold uppercase tracking-wider text-[10px]">
                                            <th class="pb-3">Guru Asal</th>
                                            <th class="pb-3">Detail Jadwal</th>
                                            <th class="pb-3">Tanggal Tukar</th>
                                            <th class="pb-3">Guru Pengganti</th>
                                            <th class="pb-3">Status</th>
                                            <th class="pb-3 text-right">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100 font-medium">
                                        <template x-for="req in swapRequests" :key="req.id">
                                            <tr>
                                                <td class="py-3 font-bold text-brand-navy" x-text="req.teacher"></td>
                                                <td class="py-3 text-[10px]" x-text="req.schedule_info"></td>
                                                <td class="py-3" x-text="req.date"></td>
                                                <td class="py-3" x-text="req.target_teacher"></td>
                                                <td class="py-3">
                                                    <span class="px-2 py-0.5 rounded-full text-[9px] font-bold"
                                                        :class="req.status === 'Disetujui' ? 'bg-emerald-50 text-brand-green border border-brand-green/20' : (req.status === 'Ditolak' ? 'bg-rose-50 text-rose-600 border border-rose-200' : 'bg-amber-50 text-amber-600 border border-amber-200') "
                                                        x-text="req.status">
                                                    </span>
                                                </td>
                                                <td class="py-3 text-right">
                                                    <template x-if="req.status === 'Diajukan' && hasAccess('swap_approve')">
                                                        <div>
                                                            <button @click="approveSwap(req.id)" class="bg-brand-green text-white px-2 py-0.5 rounded text-[10px] font-bold">Approve</button>
                                                            <button @click="rejectSwap(req.id)" class="bg-red-500 text-white px-2 py-0.5 rounded text-[10px] font-bold">Reject</button>
                                                        </div>
                                                    </template>
                                                    <template x-if="!(req.status === 'Diajukan' && hasAccess('swap_approve'))">
                                                        <span class="text-[9px] text-slate-400 italic">Selesai</span>
                                                    </template>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ========================================== -->
                <!-- MODULE 9: PAYROLL & SLIP GAJI              -->
                <!-- ========================================== -->
                <div x-show="activeTab === 'payroll'" class="space-y-6">
                    <div class="rounded-3xl border border-slate-100 bg-white p-6 shadow-sm space-y-6">
                        <div class="flex justify-between items-center">
                            <div>
                                <h3 class="text-base font-extrabold text-brand-navy tracking-tight">Modul Penggajian (Payroll)</h3>
                                <p class="text-xs text-slate-400">Hitung & kelola slip gaji berkala personil internal PPTQ.</p>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="text-xs font-bold text-slate-500">Periode Aktif:</span>
                                <span class="rounded bg-brand-sky text-brand-navy font-bold text-xs px-3 py-1 border border-brand-sky">Mei 2026</span>
                            </div>
                        </div>

                        <!-- Data table payroll -->
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-xs text-slate-600">
                                <thead>
                                    <tr class="border-b border-slate-100 text-slate-400 font-bold uppercase tracking-wider text-[10px]">
                                        <th class="pb-3">Karyawan</th>
                                        <th class="pb-3">Status</th>
                                        <th class="pb-3">Gaji Pokok</th>
                                        <th class="pb-3">Tunjangan</th>
                                        <th class="pb-3">Potongan Absen</th>
                                        <th class="pb-3 font-extrabold text-brand-navy">Gaji Bersih (Net)</th>
                                        <th class="pb-3 text-right">Berkas Slip</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 font-medium">
                                    <template x-for="p in personnel" :key="p.id">
                                        <tr>
                                            <td class="py-4">
                                                <span class="font-bold text-brand-navy block" x-text="p.name"></span>
                                                <span class="text-[9px] text-slate-400" x-text="p.jabatan"></span>
                                            </td>
                                            <td class="py-4" x-text="p.status_kerja"></td>
                                            <td class="py-4" x-text="formatRupiah(p.salary_base)"></td>
                                            <td class="py-4 text-brand-green font-bold" x-text="formatRupiah(p.salary_allowance)"></td>
                                            <td class="py-4 text-rose-500" x-text="formatRupiah(p.salary_deduction)"></td>
                                            <td class="py-4 font-bold text-brand-navy" x-text="formatRupiah(p.salary_base + p.salary_allowance - p.salary_deduction)"></td>
                                            <td class="py-4 text-right">
                                                <button @click="openSlipGaji(p)" class="inline-flex items-center gap-1.5 text-[10px] font-bold text-brand-green hover:underline">
                                                    <i class="ri-article-line"></i> Lihat Slip
                                                </button>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- ========================================== -->
                <!-- MODULE 10: DATA & KARTU SANTRI             -->
                <!-- ========================================== -->
                <div x-show="activeTab === 'santri'" class="space-y-6">
                    <div class="rounded-3xl border border-slate-100 bg-white p-6 shadow-sm space-y-6">
                        <div class="flex justify-between items-center">
                            <div>
                                <h3 class="text-base font-extrabold text-brand-navy tracking-tight font-sans">Database Santri PPTQ</h3>
                                <p class="text-xs text-slate-400">Pusat data induk peserta didik Tahfidzul Qur'an (Santri tidak memiliki hak login).</p>
                            </div>
                            <button @click="openCreateSantriModal()" class="rounded-xl bg-brand-green text-white font-bold text-xs px-4 py-2.5 shadow-sm shadow-brand-green/20 hover:bg-brand-green-dark">
                                <i class="ri-user-add-line"></i> Registrasi Santri
                            </button>
                        </div>

                        <!-- Data table santri -->
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-xs text-slate-600">
                                <thead>
                                    <tr class="border-b border-slate-100 text-slate-400 font-bold uppercase tracking-wider text-[10px]">
                                        <th class="pb-3">Nama Santri</th>
                                        <th class="pb-3">NIS / NISN</th>
                                        <th class="pb-3">Kelas Rombel</th>
                                        <th class="pb-3">Wali Orangtua</th>
                                        <th class="pb-3">Status</th>
                                        <th class="pb-3 text-right">Kartu QR</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 font-medium">
                                    <template x-for="s in santri" :key="s.id">
                                        <tr>
                                            <td class="py-4 flex items-center gap-3">
                                                <div class="h-8 w-8 rounded-full bg-slate-100 border border-slate-200 overflow-hidden flex items-center justify-center font-bold text-xs text-slate-500">
                                                    <i class="ri-user-3-line"></i>
                                                </div>
                                                <span class="font-bold text-brand-navy block" x-text="s.name"></span>
                                            </td>
                                            <td class="py-4">
                                                <span class="block" x-text="'NIS: ' + s.nis"></span>
                                                <span class="text-[9px] text-slate-400" x-text="'NISN: ' + s.nisn"></span>
                                            </td>
                                            <td class="py-4" x-text="s.class_name"></td>
                                            <td class="py-4" x-text="s.wali"></td>
                                            <td class="py-4">
                                                <span class="px-2 py-0.5 rounded-full text-[9px] font-bold"
                                                    :class="s.status === 'Aktif' ? 'bg-emerald-50 text-brand-green border border-brand-green/20' : 'bg-slate-100 text-slate-400 border border-slate-200'"
                                                    x-text="s.status">
                                                </span>
                                            </td>
                                            <td class="py-4 text-right">
                                                <button @click="generateSantriCard(s)" class="inline-flex items-center gap-1.5 text-[10px] font-bold text-brand-green hover:underline">
                                                    <i class="ri-qr-code-line text-sm"></i> Generate Kartu
                                                </button>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- ========================================== -->
                <!-- MODULE 11: SCAN ABSENSI SANTRI QR          -->
                <!-- ========================================== -->
                <div x-show="activeTab === 'presence-santri'" class="space-y-6">
                    <div class="grid gap-6 lg:grid-cols-12">
                        <!-- Barcode scanner simulator -->
                        <div class="lg:col-span-5 rounded-3xl border border-slate-100 bg-white p-6 shadow-sm space-y-4">
                            <h4 class="text-xs font-extrabold text-brand-navy uppercase tracking-wider">Simulator Kamera Scan QR</h4>
                            <p class="text-[11px] text-slate-400">Pilih santri untuk mensimulasikan tembakan barcode scanner kartu santri fisik.</p>
                            
                            <div class="space-y-4 pt-2">
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-2">Pilih Santri yang Hadir</label>
                                    <select id="scan_santri_select" class="block w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-3 text-xs text-slate-700 focus:outline-none">
                                        <template x-for="s in santri.filter(st => st.status === 'Aktif')" :key="s.id">
                                            <option :value="s.id" x-text="s.name + ' (' + s.class_name + ')'"></option>
                                        </template>
                                    </select>
                                </div>

                                <!-- Scanning graphic -->
                                <div class="border border-dashed border-slate-200 rounded-2xl h-44 bg-slate-900 overflow-hidden relative flex flex-col justify-center items-center text-white">
                                    <div class="qr-scanner-line absolute top-0 left-0 right-0 h-1 bg-red-500 shadow-md shadow-red-500/80 pointer-events-none"></div>
                                    <i class="ri-qr-scan-2-line text-4xl text-brand-green/80 animate-pulse"></i>
                                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-3">Kamera Scanner Aktif</span>
                                </div>

                                <button @click="triggerSantriScan()" class="w-full rounded-xl bg-brand-green py-3 text-xs font-bold text-white shadow-md shadow-brand-green/10 hover:bg-brand-green-dark transition-all">
                                    <i class="ri-barcode-box-line"></i> Simulasikan Tembak Barcode
                                </button>
                            </div>
                        </div>

                        <!-- Scan records list -->
                        <div class="lg:col-span-7 rounded-3xl border border-slate-100 bg-white p-6 shadow-sm space-y-4">
                            <h4 class="text-xs font-extrabold text-brand-navy uppercase tracking-wider">Log Riwayat Scan Absensi Santri</h4>
                            
                            <div class="overflow-x-auto max-h-[380px] overflow-y-auto pr-1">
                                <table class="w-full text-left text-xs text-slate-600">
                                    <thead>
                                        <tr class="border-b border-slate-100 text-slate-400 font-bold uppercase tracking-wider text-[10px]">
                                            <th class="pb-3">Tanggal Hari</th>
                                            <th class="pb-3">Jam Scan</th>
                                            <th class="pb-3">Nama Santri</th>
                                            <th class="pb-3">Rombel Kelas</th>
                                            <th class="pb-3">Validasi Status</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100 font-medium">
                                        <template x-for="log in santriPresence" :key="log.id">
                                            <tr>
                                                <td class="py-3" x-text="log.date"></td>
                                                <td class="py-3 text-brand-navy font-bold" x-text="log.time"></td>
                                                <td class="py-3 font-bold" x-text="log.name"></td>
                                                <td class="py-3" x-text="log.class_name"></td>
                                                <td class="py-3">
                                                    <span class="rounded-full bg-emerald-50 border border-emerald-200 text-brand-green font-bold text-[9px] px-2 py-0.5 inline-block">Hadir</span>
                                                </td>
                                            </tr>
                                        </template>
                                        <template x-if="santriPresence.length === 0">
                                            <tr>
                                                <td colspan="5" class="py-8 text-center text-slate-400 italic">Belum ada santri terabsen melalui scan hari ini. Silakan coba tembak barcode.</td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ========================================== -->
                <!-- MODULE 12: KELAS ROMBEL                    -->
                <!-- ========================================== -->
                <div x-show="activeTab === 'classes'" class="space-y-6">
                    <div class="rounded-3xl border border-slate-100 bg-white p-6 shadow-sm space-y-6">
                        <div class="flex justify-between items-center">
                            <div>
                                <h3 class="text-base font-extrabold text-brand-navy tracking-tight">Rombongan Belajar (Rombel Kelas)</h3>
                                <p class="text-xs text-slate-400">Pengelompokan santri per kelas, wali kelas penanggung jawab, dan riwayat status kelas.</p>
                            </div>
                        </div>

                        <div class="grid gap-6 md:grid-cols-3">
                            <template x-for="cls in classes" :key="cls.name">
                                <div class="rounded-2xl border border-slate-100 bg-slate-50 p-5 space-y-4">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <span class="rounded bg-brand-navy text-white font-bold text-[9px] px-2 py-0.5 uppercase tracking-wide inline-block" x-text="'Tingkat ' + cls.grade"></span>
                                            <h4 class="text-lg font-bold text-brand-navy mt-1.5" x-text="'Kelas ' + cls.name"></h4>
                                        </div>
                                        <i class="ri-door-open-line text-2xl text-slate-300"></i>
                                    </div>
                                    <div class="space-y-1">
                                        <span class="text-[9px] font-bold uppercase tracking-wider text-slate-400 block">Wali Kelas</span>
                                        <span class="text-xs font-bold text-slate-700" x-text="cls.walikelas"></span>
                                    </div>
                                    <div class="border-t border-slate-200/60 pt-3 flex justify-between items-center text-xs font-bold text-slate-500">
                                        <span>Total Santri:</span>
                                        <span class="text-brand-green" x-text="santri.filter(s => s.class_name === cls.name).length">0</span>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- ========================================== -->
                <!-- MODULE 13: PERILAKU & POIN (BEHAVIORS)     -->
                <!-- ========================================== -->
                <div x-show="activeTab === 'behaviors'" class="space-y-6">
                    <div class="grid gap-6 lg:grid-cols-12">
                        <!-- Input Log Form -->
                        <div class="lg:col-span-4 rounded-3xl border border-slate-100 bg-white p-6 shadow-sm space-y-4">
                            <h4 class="text-xs font-extrabold text-brand-navy uppercase tracking-wider">Catat Perilaku Santri</h4>
                            
                            <form onsubmit="handleBehaviorForm(event)" class="space-y-4">
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-2">Pilih Santri</label>
                                    <select id="behavior_santri" class="block w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-3 text-xs text-slate-700 focus:outline-none">
                                        <template x-for="s in santri" :key="s.id">
                                            <option :value="s.name" x-text="s.name"></option>
                                        </template>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-2">Kategori Perilaku</label>
                                    <div class="grid grid-cols-2 gap-2">
                                        <button type="button" @click="behaviorFormType = 'Pelanggaran'"
                                            class="py-2.5 rounded-xl text-xs font-bold border transition-all"
                                            :class="behaviorFormType === 'Pelanggaran' ? 'bg-rose-500 text-white border-rose-500' : 'bg-slate-50 text-slate-600 border-slate-200'">
                                            Pelanggaran
                                        </button>
                                        <button type="button" @click="behaviorFormType = 'Kebaikan'"
                                            class="py-2.5 rounded-xl text-xs font-bold border transition-all"
                                            :class="behaviorFormType === 'Kebaikan' ? 'bg-brand-green text-white border-brand-green' : 'bg-slate-50 text-slate-600 border-slate-200'">
                                            Kebaikan
                                        </button>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-2">Bobot Poin</label>
                                    <input type="number" id="behavior_points" min="1" max="100" required class="block w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-xs text-slate-700 focus:outline-none">
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-2">Keterangan Catatan</label>
                                    <textarea id="behavior_note" placeholder="Contoh: Terlambat shalat berjamaah..." rows="2" required class="block w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-xs text-slate-700 focus:outline-none"></textarea>
                                </div>
                                <button type="submit" class="w-full rounded-xl bg-brand-navy py-3 text-xs font-bold text-white shadow-md hover:bg-brand-navy-dark">Simpan Log Perilaku</button>
                            </form>
                        </div>

                        <!-- Data List -->
                        <div class="lg:col-span-8 rounded-3xl border border-slate-100 bg-white p-6 shadow-sm space-y-4">
                            <h4 class="text-xs font-extrabold text-brand-navy uppercase tracking-wider">Log Perilaku Santri Terkini</h4>
                            
                            <div class="overflow-x-auto">
                                <table class="w-full text-left text-xs text-slate-600">
                                    <thead>
                                        <tr class="border-b border-slate-100 text-slate-400 font-bold uppercase tracking-wider text-[10px]">
                                            <th class="pb-3">Tanggal</th>
                                            <th class="pb-3">Nama Santri</th>
                                            <th class="pb-3">Tipe</th>
                                            <th class="pb-3">Bobot Poin</th>
                                            <th class="pb-3">Keterangan</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100 font-medium">
                                        <template x-for="b in behaviors" :key="b.id">
                                            <tr>
                                                <td class="py-3" x-text="b.date"></td>
                                                <td class="py-3 font-bold text-brand-navy" x-text="b.name"></td>
                                                <td class="py-3">
                                                    <span class="px-2 py-0.5 rounded-full text-[9px] font-bold"
                                                        :class="b.type === 'Kebaikan' ? 'bg-emerald-50 text-brand-green border border-brand-green/20' : 'bg-rose-50 text-rose-600 border border-rose-200'"
                                                        x-text="b.type">
                                                    </span>
                                                </td>
                                                <td class="py-3 font-bold" :class="b.type === 'Kebaikan' ? 'text-brand-green' : 'text-rose-500'" x-text="(b.type === 'Kebaikan' ? '+' : '-') + b.points + ' Poin'"></td>
                                                <td class="py-3 text-[10px]" x-text="b.note"></td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ========================================== -->
                <!-- MODULE 14: NILAI & PERKEMBANGAN            -->
                <!-- ========================================== -->
                <div x-show="activeTab === 'grades'" class="space-y-6">
                    <div class="grid gap-6 lg:grid-cols-12">
                        <!-- Grading Form -->
                        <div class="lg:col-span-4 rounded-3xl border border-slate-100 bg-white p-6 shadow-sm space-y-4">
                            <h4 class="text-xs font-extrabold text-brand-navy uppercase tracking-wider">Input Nilai & Perkembangan</h4>
                            
                            <form onsubmit="handleGradeForm(event)" class="space-y-4">
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-2">Nama Santri</label>
                                    <select id="grade_santri" class="block w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-3 text-xs text-slate-700 focus:outline-none">
                                        <template x-for="s in santri" :key="s.id">
                                            <option :value="s.name" x-text="s.name"></option>
                                        </template>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-2">Halaqah / Mapel</label>
                                    <select id="grade_subject" class="block w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-3 text-xs text-slate-700 focus:outline-none">
                                        <option value="Tahfidz Al-Qur'an">Tahfidz Al-Qur'an</option>
                                        <option value="Bahasa Arab Nahwu">Bahasa Arab Nahwu</option>
                                        <option value="Hadits Arbain">Hadits Arbain</option>
                                        <option value="Fiqih Ibadah">Fiqih Ibadah</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-2">Nilai Angka (1-100)</label>
                                    <input type="number" id="grade_score" min="1" max="100" required class="block w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-xs text-slate-700 focus:outline-none">
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-2">Catatan Kualitatif</label>
                                    <textarea id="grade_note" placeholder="Contoh: Lancar hafalan Juz 30..." rows="2" required class="block w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-xs text-slate-700 focus:outline-none"></textarea>
                                </div>
                                <button type="submit" class="w-full rounded-xl bg-brand-navy py-3 text-xs font-bold text-white shadow-md hover:bg-brand-navy-dark">Simpan Nilai</button>
                            </form>
                        </div>

                        <!-- Data List -->
                        <div class="lg:col-span-8 rounded-3xl border border-slate-100 bg-white p-6 shadow-sm space-y-4">
                            <h4 class="text-xs font-extrabold text-brand-navy uppercase tracking-wider">Rekap Evaluasi Perkembangan Santri</h4>
                            
                            <div class="overflow-x-auto">
                                <table class="w-full text-left text-xs text-slate-600">
                                    <thead>
                                        <tr class="border-b border-slate-100 text-slate-400 font-bold uppercase tracking-wider text-[10px]">
                                            <th class="pb-3">Tanggal</th>
                                            <th class="pb-3">Nama Santri</th>
                                            <th class="pb-3">Mapel / Sesi</th>
                                            <th class="pb-3">Nilai</th>
                                            <th class="pb-3">Catatan Perkembangan</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100 font-medium">
                                        <template x-for="g in grades" :key="g.id">
                                            <tr>
                                                <td class="py-3" x-text="g.date"></td>
                                                <td class="py-3 font-bold text-brand-navy" x-text="g.name"></td>
                                                <td class="py-3" x-text="g.subject"></td>
                                                <td class="py-3 font-bold text-brand-green" x-text="g.score"></td>
                                                <td class="py-3 text-[10px]" x-text="g.note"></td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ========================================== -->
                <!-- MODULE 15: KUNJUNGAN WALI SANTRI           -->
                <!-- ========================================== -->
                <div x-show="activeTab === 'visits'" class="space-y-6">
                    <div class="grid gap-6 lg:grid-cols-12">
                        <!-- Log Visitors Form -->
                        <div class="lg:col-span-4 rounded-3xl border border-slate-100 bg-white p-6 shadow-sm space-y-4">
                            <h4 class="text-xs font-extrabold text-brand-navy uppercase tracking-wider">Catat Kunjungan / Jenguk</h4>
                            
                            <form onsubmit="handleVisitForm(event)" class="space-y-4">
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-2">Nama Santri</label>
                                    <select id="visit_santri" class="block w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-3 text-xs text-slate-700 focus:outline-none">
                                        <template x-for="s in santri" :key="s.id">
                                            <option :value="s.name" x-text="s.name"></option>
                                        </template>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-2">Nama Pengunjung (Wali)</label>
                                    <input type="text" id="visit_name" placeholder="Nama wali..." required class="block w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-xs text-slate-700 focus:outline-none">
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-2">Hubungan dengan Santri</label>
                                    <select id="visit_relation" class="block w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-3 text-xs text-slate-700 focus:outline-none">
                                        <option value="Ayah Kandung">Ayah Kandung</option>
                                        <option value="Ibu Kandung">Ibu Kandung</option>
                                        <option value="Paman / Bibi">Paman / Bibi</option>
                                        <option value="Kakak Kandung">Kakak Kandung</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-2">Tujuan Kunjungan</label>
                                    <textarea id="visit_note" placeholder="Mengantar keperluan..." rows="2" required class="block w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-xs text-slate-700 focus:outline-none"></textarea>
                                </div>
                                <button type="submit" class="w-full rounded-xl bg-brand-navy py-3 text-xs font-bold text-white shadow-md hover:bg-brand-navy-dark">Catat Jengukan</button>
                            </form>
                        </div>

                        <!-- Data List -->
                        <div class="lg:col-span-8 rounded-3xl border border-slate-100 bg-white p-6 shadow-sm space-y-4">
                            <h4 class="text-xs font-extrabold text-brand-navy uppercase tracking-wider">Log Jengukan Wali Santri Hari Ini</h4>
                            
                            <div class="overflow-x-auto">
                                <table class="w-full text-left text-xs text-slate-600">
                                    <thead>
                                        <tr class="border-b border-slate-100 text-slate-400 font-bold uppercase tracking-wider text-[10px]">
                                            <th class="pb-3">Waktu Log</th>
                                            <th class="pb-3">Nama Santri</th>
                                            <th class="pb-3">Nama Pengunjung</th>
                                            <th class="pb-3">Hubungan</th>
                                            <th class="pb-3">Tujuan Keperluan</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100 font-medium">
                                        <template x-for="v in visits" :key="v.id">
                                            <tr>
                                                <td class="py-3" x-text="v.date"></td>
                                                <td class="py-3 font-bold text-brand-navy" x-text="v.name"></td>
                                                <td class="py-3 font-bold" x-text="v.visitor"></td>
                                                <td class="py-3" x-text="v.relation"></td>
                                                <td class="py-3 text-[10px]" x-text="v.note"></td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ========================================== -->
                <!-- MODULE 16: ANNOUNCEMENT SETTINGS           -->
                <!-- ========================================== -->
                <div x-show="activeTab === 'announcements'" class="space-y-6">
                    <div class="grid gap-6 lg:grid-cols-12">
                        <!-- Create Form for Admin -->
                        <div x-show="hasAccess('personnel_manage')" class="lg:col-span-5 rounded-3xl border border-slate-100 bg-white p-6 shadow-sm space-y-4">
                            <h4 class="text-xs font-extrabold text-brand-navy uppercase tracking-wider">Buat Pengumuman Baru</h4>
                            
                            <form onsubmit="handleAnnouncementForm(event)" class="space-y-4">
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-2">Judul Pengumuman</label>
                                    <input type="text" id="ann_title" required placeholder="Judul..." class="block w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-xs text-slate-700 focus:outline-none">
                                </div>
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-2">Target Pengguna</label>
                                        <select id="ann_target" class="block w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-xs text-slate-700 focus:outline-none">
                                            <option value="Semua">Semua</option>
                                            <option value="Guru">Guru / Ustadz</option>
                                            <option value="Staff">Staff Kantor</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-2">Tanggal Publikasi</label>
                                        <input type="date" id="ann_date" required class="block w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-xs text-slate-700 focus:outline-none">
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-2">Isi Pengumuman</label>
                                    <textarea id="ann_content" placeholder="Teks pengumuman..." rows="4" required class="block w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-xs text-slate-700 focus:outline-none"></textarea>
                                </div>
                                <button type="submit" class="w-full rounded-xl bg-brand-navy py-3 text-xs font-bold text-white shadow-md hover:bg-brand-navy-dark">Siarkan Informasi</button>
                            </form>
                        </div>

                        <!-- Announcement List -->
                        <div class="rounded-3xl border border-slate-100 bg-white p-6 shadow-sm space-y-4"
                             :class="hasAccess('personnel_manage') ? 'lg:col-span-7' : 'lg:col-span-12'">
                            <h4 class="text-xs font-extrabold text-brand-navy uppercase tracking-wider font-sans">Daftar Pengumuman Aktif</h4>
                            
                            <div class="space-y-4">
                                <template x-for="ann in announcements" :key="ann.id">
                                    <div class="p-4 rounded-2xl border border-slate-100 bg-slate-50/50 space-y-3 relative hover:border-brand-navy transition-all">
                                        <div class="flex justify-between items-center text-[9px] font-bold text-slate-400">
                                            <span class="rounded bg-brand-navy text-white px-2 py-0.5 uppercase tracking-wider" x-text="ann.target"></span>
                                            <span x-text="ann.date"></span>
                                        </div>
                                        <h5 class="text-sm font-bold text-brand-navy" x-text="ann.title"></h5>
                                        <p class="text-xs text-slate-500 leading-relaxed" x-text="ann.content"></p>
                                        
                                        <!-- Actions -->
                                        <div class="flex justify-end gap-3 border-t border-slate-100 pt-2.5 text-[10px] font-bold text-slate-400">
                                            <template x-if="hasAccess('personnel_manage')">
                                                <button @click="toggleAnnActive(ann.id)" 
                                                    :class="ann.active ? 'text-slate-500 hover:text-brand-navy' : 'text-brand-green hover:underline'"
                                                    x-text="ann.active ? 'Nonaktifkan' : 'Aktifkan'"></button>
                                            </template>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ========================================== -->
                <!-- MODULE 17: REPORTS & EXPORTS               -->
                <!-- ========================================== -->
                <div x-show="activeTab === 'reports'" class="space-y-6">
                    <div class="rounded-3xl border border-slate-100 bg-white p-6 shadow-sm space-y-6">
                        <div class="flex justify-between items-center">
                            <div>
                                <h3 class="text-base font-extrabold text-brand-navy tracking-tight">Ekspor Laporan Strategis</h3>
                                <p class="text-xs text-slate-400">Dapatkan data terstruktur terfilter untuk pelaporan rapat pimpinan pondok.</p>
                            </div>
                        </div>

                        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
                            <!-- Report Item 1 -->
                            <div class="p-5 rounded-2xl border border-slate-100 bg-slate-50/50 flex flex-col justify-between h-40">
                                <div>
                                    <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-brand-sky text-brand-navy text-xl mb-3">
                                        <i class="ri-user-star-line"></i>
                                    </div>
                                    <h4 class="text-xs font-extrabold text-brand-navy uppercase tracking-wider">Rekap Kehadiran GPS</h4>
                                    <p class="text-[10px] text-slate-400 mt-1 leading-relaxed">Persentase kehadiran bulanan, keterlambatan, dan jam kerja.</p>
                                </div>
                                <button @click="triggerToast('success', 'File Rekap_Absensi_Karyawan_Mei2026.xlsx sedang diekspor...')" class="self-start text-[10px] font-bold text-brand-green hover:underline">
                                    <i class="ri-download-2-line"></i> Export Excel
                                </button>
                            </div>

                            <!-- Report Item 2 -->
                            <div class="p-5 rounded-2xl border border-slate-100 bg-slate-50/50 flex flex-col justify-between h-40">
                                <div>
                                    <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-50 text-brand-green text-xl mb-3">
                                        <i class="ri-contacts-line"></i>
                                    </div>
                                    <h4 class="text-xs font-extrabold text-brand-navy uppercase tracking-wider">Induk Data Santri</h4>
                                    <p class="text-[10px] text-slate-400 mt-1 leading-relaxed">Profil biodata santri lengkap per kelas rombongan belajar.</p>
                                </div>
                                <button @click="triggerToast('success', 'File Induk_Santri_2026.xlsx sedang diekspor...')" class="self-start text-[10px] font-bold text-brand-green hover:underline">
                                    <i class="ri-download-2-line"></i> Export Excel
                                </button>
                            </div>

                            <!-- Report Item 3 -->
                            <div class="p-5 rounded-2xl border border-slate-100 bg-slate-50/50 flex flex-col justify-between h-40">
                                <div>
                                    <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-amber-50 text-amber-600 text-xl mb-3">
                                        <i class="ri-shield-star-line"></i>
                                    </div>
                                    <h4 class="text-xs font-extrabold text-brand-navy uppercase tracking-wider">Laporan Poin Santri</h4>
                                    <p class="text-[10px] text-slate-400 mt-1 leading-relaxed">Rekapitulasi poin kebaikan dan akumulasi pelanggaran santri.</p>
                                </div>
                                <button @click="triggerToast('success', 'File Laporan_Poin_Perilaku_Santri.xlsx sedang diekspor...')" class="self-start text-[10px] font-bold text-brand-green hover:underline">
                                    <i class="ri-download-2-line"></i> Export Excel
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ========================================== -->
                <!-- MODULE 18: WHATSAPP FONNTE GATEWAY INTEGRATION -->
                <!-- ========================================== -->
                <div x-show="activeTab === 'whatsapp'" class="space-y-6">
                    <div class="rounded-3xl border border-slate-100 bg-white p-6 shadow-sm space-y-6">
                        <div class="flex justify-between items-center">
                            <div>
                                <h3 class="text-base font-extrabold text-brand-navy tracking-tight">Koneksi WhatsApp Gateway (Fonnte API)</h3>
                                <p class="text-xs text-slate-400">Hubungkan sistem kepondokan dengan nomor Whatsapp dinas menggunakan layanan Fonnte untuk notifikasi otomatis.</p>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-bold text-slate-500">Status Gateway:</span>
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold flex items-center gap-1.5"
                                    :class="whatsappConnected ? 'bg-emerald-50 text-brand-green border border-brand-green/20' : 'bg-rose-50 text-rose-600 border border-rose-200'">
                                    <span class="h-1.5 w-1.5 rounded-full" :class="whatsappConnected ? 'bg-brand-green animate-pulse' : 'bg-rose-500'"></span>
                                    <span x-text="whatsappConnected ? 'Terhubung (Active)' : 'Terputus (Offline)'"></span>
                                </span>
                            </div>
                        </div>

                        <div class="grid md:grid-cols-2 gap-8">
                            <!-- Left panel: API configuration -->
                            <div class="space-y-4">
                                <h4 class="text-xs font-extrabold text-brand-navy uppercase tracking-wider border-b pb-2">Pengaturan Token API</h4>
                                
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-2">Fonnte API Token / App Key</label>
                                        <div class="relative rounded-xl shadow-sm">
                                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                                <i class="ri-key-2-line"></i>
                                            </div>
                                            <input type="password" x-model="whatsappToken" placeholder="Masukkan token fonnte..." class="block w-full rounded-xl border border-slate-200 bg-slate-50 pl-10 pr-3 py-2.5 text-xs text-slate-700 focus:outline-none">
                                        </div>
                                        <p class="text-[9px] text-slate-400 mt-1">Dapatkan token API Anda dari dashboard fonnte.com</p>
                                    </div>

                                    <div>
                                        <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-2">Nomor HP Pengirim (Device)</label>
                                        <input type="text" x-model="whatsappSender" placeholder="Contoh: 081234567890" class="block w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-xs text-slate-700 focus:outline-none">
                                    </div>

                                    <div class="pt-2 flex gap-3">
                                        <button @click="testWhatsappConnection()" class="rounded-xl border border-slate-200 bg-white hover:bg-slate-50 px-4 py-2.5 text-xs font-bold text-slate-600 transition-all flex items-center gap-1.5">
                                            <i class="ri-refresh-line"></i>
                                            Simulasikan Toggle Koneksi
                                        </button>
                                        <button @click="triggerToast('success', 'Pengaturan token Fonnte berhasil disimpan ke konfigurasi.')" class="rounded-xl bg-brand-navy text-white hover:bg-brand-navy-dark px-4 py-2.5 text-xs font-bold transition-all shadow-md">
                                            Simpan Token
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Right panel: send test message -->
                            <div class="space-y-4">
                                <h4 class="text-xs font-extrabold text-brand-navy uppercase tracking-wider border-b pb-2">Uji Coba Kirim Pesan</h4>
                                
                                <form @submit="sendTestWhatsappMessage(event)" class="space-y-3">
                                    <div>
                                        <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-2">Nomor HP Penerima (Tujuan)</label>
                                        <div class="relative rounded-xl shadow-sm">
                                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                                <i class="ri-whatsapp-line"></i>
                                            </div>
                                            <input type="text" id="wa_test_phone" required placeholder="Contoh: 0812XXXXXXXX" class="block w-full rounded-xl border border-slate-200 bg-slate-50 pl-10 pr-3 py-2.5 text-xs text-slate-700 focus:outline-none">
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-2">Isi Pesan Uji Coba</label>
                                        <textarea id="wa_test_message" required placeholder="Tulis pesan uji coba di sini..." rows="3" class="block w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-xs text-slate-700 focus:outline-none"></textarea>
                                    </div>
                                    <button type="submit" class="rounded-xl bg-brand-green text-white hover:bg-brand-green-dark px-4 py-2.5 text-xs font-bold transition-all shadow-md flex items-center gap-1.5">
                                        <i class="ri-send-plane-line"></i>
                                        Kirim Pesan WA
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ========================================== -->
                <!-- MODULE 19: LANDING PAGE & BRANDING MANAGER -->
                <!-- ========================================== -->
                <div x-show="activeTab === 'branding'" class="space-y-6">
                    <div class="rounded-3xl border border-slate-100 bg-white p-6 shadow-sm space-y-6">
                        <div class="flex justify-between items-center">
                            <div>
                                <h3 class="text-base font-extrabold text-brand-navy tracking-tight font-sans">Pengaturan Landing Page & Logo Branding</h3>
                                <p class="text-xs text-slate-400">Atur informasi, logo instansi, serta gambar hero banner yang akan diterapkan secara global (Landing Page, Login, dan Dashboard).</p>
                            </div>
                            <button @click="saveBrandingSettings()" class="rounded-xl bg-brand-green text-white font-bold text-xs px-5 py-3 shadow-md shadow-brand-green/20 hover:bg-brand-green-dark transition-all">
                                <i class="ri-save-line"></i> Simpan & Publikasikan
                            </button>
                        </div>

                        <div class="grid lg:grid-cols-12 gap-8">
                            <!-- Left: Configurations Forms -->
                            <div class="lg:col-span-7 space-y-6">
                                <!-- Logo settings -->
                                <div class="space-y-4">
                                    <h4 class="text-xs font-extrabold text-brand-navy uppercase tracking-wider border-b pb-2">1. Konfigurasi Logo Aplikasi</h4>
                                    
                                    <div class="grid sm:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-2">Tipe Logo Utama</label>
                                            <select x-model="logoType" class="block w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-xs text-slate-700 focus:outline-none">
                                                <option value="text">Teks (Inisial)</option>
                                                <option value="image">Gambar (Logo Instansi)</option>
                                            </select>
                                        </div>
                                        <div x-show="logoType === 'text'">
                                            <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-2">Teks Inisial Logo</label>
                                            <input type="text" x-model="logoText" placeholder="Contoh: NI" class="block w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2 text-xs text-slate-700 focus:outline-none">
                                        </div>
                                        <div x-show="logoType === 'image'">
                                            <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-2">Upload Logo Gambar</label>
                                            <div class="flex items-center gap-2">
                                                <label class="flex items-center gap-2 px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl border border-slate-300 cursor-pointer transition-colors text-xs font-bold">
                                                    <i class="ri-upload-2-line"></i> Pilih File Logo
                                                    <input type="file" @change="uploadLogoFile($event)" accept="image/*" class="hidden">
                                                </label>
                                                <button type="button" @click="logoImage = ''" class="px-3 py-2 text-red-600 hover:bg-red-50 rounded-xl text-xs font-bold transition-colors" x-show="logoImage">
                                                    <i class="ri-delete-bin-line"></i> Hapus
                                                </button>
                                            </div>
                                            <span class="text-[9px] text-slate-400 mt-1 block">Rekomendasi: Format PNG/JPG transparan, ukuran &lt; 500KB.</span>
                                        </div>
                                    </div>

                                    <div class="grid sm:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-2">Nama Instansi Pondok</label>
                                            <input type="text" x-model="pondokName" placeholder="Contoh: PPTQ Nurul Iman" class="block w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2 text-xs text-slate-700 focus:outline-none">
                                        </div>
                                        <div>
                                            <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-2">Tagline Instansi</label>
                                            <input type="text" x-model="pondokTagline" placeholder="Contoh: Tahfidzul Qur'an" class="block w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2 text-xs text-slate-700 focus:outline-none">
                                        </div>
                                    </div>
                                </div>

                                <!-- Landing page hero settings -->
                                <div class="space-y-4 pt-4">
                                    <h4 class="text-xs font-extrabold text-brand-navy uppercase tracking-wider border-b pb-2">2. Informasi & Hero Landing Page</h4>
                                    
                                    <div class="grid sm:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-2">Judul Hero (Baris 1)</label>
                                            <input type="text" x-model="landingTitle" placeholder="Contoh: Membangun Generasi" class="block w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2 text-xs text-slate-700 focus:outline-none">
                                        </div>
                                        <div>
                                            <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-2">Judul Hero Highlight (Baris 2)</label>
                                            <input type="text" x-model="landingTitleHighlight" placeholder="Contoh: Qur'ani & Unggul" class="block w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2 text-xs text-slate-700 focus:outline-none">
                                        </div>
                                    </div>

                                    <div>
                                        <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-2">Deskripsi Hero</label>
                                        <textarea x-model="landingDesc" rows="3" class="block w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2 text-xs text-slate-700 focus:outline-none"></textarea>
                                    </div>

                                    <div>
                                        <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-2">Pilih Gambar Hero Banner</label>
                                        <div class="grid grid-cols-2 gap-3 mb-3">
                                            <button @click="landingHeroImage = 'default'" class="py-2.5 rounded-xl text-xs font-bold border transition-all"
                                                :class="landingHeroImage === 'default' ? 'bg-brand-navy text-white border-brand-navy' : 'bg-slate-50 text-slate-600 border-slate-200'">
                                                Default Glassmorphic Card
                                            </button>
                                            <button @click="landingHeroImage = 'custom'" class="py-2.5 rounded-xl text-xs font-bold border transition-all"
                                                :class="landingHeroImage === 'custom' ? 'bg-brand-navy text-white border-brand-navy' : 'bg-slate-50 text-slate-600 border-slate-200'">
                                                Gambar Kustom (Desain Vektor)
                                            </button>
                                        </div>

                                        <div x-show="landingHeroImage === 'custom'" class="space-y-2 border border-slate-200 bg-slate-50 p-3.5 rounded-2xl">
                                            <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Upload Gambar Hero Custom</label>
                                            <div class="flex items-center gap-2">
                                                <label class="flex items-center gap-2 px-3 py-2 bg-white hover:bg-slate-100 text-slate-700 rounded-xl border border-slate-300 cursor-pointer transition-colors text-xs font-bold shadow-sm">
                                                    <i class="ri-image-add-line"></i> Pilih Gambar Hero
                                                    <input type="file" @change="uploadHeroFile($event)" accept="image/*" class="hidden">
                                                </label>
                                                <button type="button" @click="landingHeroImageCustom = '/pondok_hero_banner.png'" class="px-3 py-2 text-slate-500 hover:bg-slate-200 rounded-xl text-xs font-bold transition-colors" x-show="landingHeroImageCustom && landingHeroImageCustom !== '/pondok_hero_banner.png'">
                                                    Reset ke Default
                                                </button>
                                            </div>
                                            <span class="text-[9px] text-slate-400 block">Rekomendasi: Dimensi landscape (16:9), ukuran &lt; 1.5MB.</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Statistics count settings -->
                                <div class="space-y-4 pt-4">
                                    <h4 class="text-xs font-extrabold text-brand-navy uppercase tracking-wider border-b pb-2">3. Statistik Landing Page</h4>
                                    
                                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                                        <div>
                                            <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-2">Total Staff/Guru</label>
                                            <input type="text" x-model="statsPersonnel" class="block w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2 text-xs text-slate-700 focus:outline-none">
                                        </div>
                                        <div>
                                            <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-2">Total Santri</label>
                                            <input type="text" x-model="statsSantri" class="block w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2 text-xs text-slate-700 focus:outline-none">
                                        </div>
                                        <div>
                                            <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-2">Total Halaqah</label>
                                            <input type="text" x-model="statsHalaqah" class="block w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2 text-xs text-slate-700 focus:outline-none">
                                        </div>
                                        <div>
                                            <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-2">Akurasi Data</label>
                                            <input type="text" x-model="statsAccuracy" class="block w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2 text-xs text-slate-700 focus:outline-none">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Right: Live Preview Panel -->
                            <div class="lg:col-span-5 space-y-4">
                                <h4 class="text-xs font-extrabold text-brand-navy uppercase tracking-wider border-b pb-2">Pratinjau Hasil (Live Preview)</h4>
                                
                                <div class="border rounded-2xl overflow-hidden shadow-md">
                                    <div class="bg-slate-900 px-4 py-2 flex items-center justify-between text-slate-400 text-[10px] font-bold">
                                        <span>PRATINJAU HEADER LANDING PAGE</span>
                                        <i class="ri-window-line"></i>
                                    </div>
                                    <div class="p-4 bg-white border-b flex items-center justify-between">
                                        <!-- Mock Header Logo Preview -->
                                        <div class="flex items-center gap-2">
                                            <div x-show="logoType === 'text'" class="flex h-8 w-8 items-center justify-center rounded-lg bg-gradient-to-br from-brand-navy to-brand-navy-dark text-white font-bold text-sm" x-text="logoText"></div>
                                            <div x-show="logoType === 'image'" class="h-8 w-8 rounded-lg overflow-hidden border">
                                                <img :src="logoImage || '/favicon.ico'" class="h-full w-full object-cover">
                                            </div>
                                            <div>
                                                <span class="text-xs font-bold text-brand-navy block leading-none" x-text="pondokName"></span>
                                                <span class="text-[8px] uppercase tracking-wider text-brand-green font-semibold" x-text="pondokTagline"></span>
                                            </div>
                                        </div>
                                        <span class="text-[9px] font-bold bg-slate-100 text-slate-500 px-2 py-0.5 rounded">Menu</span>
                                    </div>
                                </div>

                                <div class="border rounded-2xl overflow-hidden shadow-md bg-gradient-to-br from-brand-navy to-[#0c266f] text-white">
                                    <div class="bg-slate-900/50 px-4 py-2 flex items-center justify-between text-slate-300 text-[10px] font-bold border-b border-white/5">
                                        <span>PRATINJAU HERO BANNER LANDING</span>
                                        <i class="ri-image-line"></i>
                                    </div>
                                    <div class="p-6 space-y-6">
                                        <div class="space-y-2">
                                            <h3 class="text-base font-extrabold leading-tight">
                                                <span x-text="landingTitle"></span> <br/>
                                                <span class="bg-gradient-to-r from-brand-sky via-teal-200 to-brand-green bg-clip-text text-transparent" x-text="landingTitleHighlight"></span>
                                            </h3>
                                            <p class="text-[10px] text-slate-300 line-clamp-3 leading-relaxed" x-text="landingDesc"></p>
                                        </div>

                                        <div class="flex justify-center border border-white/10 rounded-xl bg-white/5 p-2">
                                            <!-- Image preview toggle -->
                                            <div x-show="landingHeroImage === 'default'" class="text-center py-6 text-slate-300">
                                                <i class="ri-slideshow-3-line text-2xl mb-1 text-brand-green"></i>
                                                <span class="text-[9px] block">Default Glassmorphic Card Active</span>
                                            </div>
                                            <div x-show="landingHeroImage === 'custom'" class="w-full h-24 overflow-hidden rounded-lg flex items-center justify-center">
                                                <img :src="landingHeroImageCustom || '/pondok_hero_banner.png'" class="h-full w-full object-cover">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ========================================== -->
                <!-- MODULE 20: PROFILE & PASSWORD              -->
                <!-- ========================================== -->
                <div x-show="activeTab === 'profile'" class="space-y-6">
                    <div class="rounded-3xl border border-slate-100 bg-white p-6 shadow-sm space-y-6">
                        <div>
                            <h3 class="text-base font-extrabold text-brand-navy tracking-tight">Akun Profil Saya</h3>
                            <p class="text-xs text-slate-400">Kelola kredensial dan kata sandi masuk portal Anda.</p>
                        </div>

                        <div class="grid md:grid-cols-2 gap-8">
                            <!-- Left: User details -->
                            <div class="space-y-4">
                                <h4 class="text-xs font-extrabold text-brand-navy uppercase tracking-wider border-b pb-2">Informasi Akun</h4>
                                
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <span class="text-[9px] font-bold uppercase tracking-wider text-slate-400 block">Nama Lengkap</span>
                                        <span class="text-xs font-bold text-slate-700" x-text="currentUser.name"></span>
                                    </div>
                                    <div>
                                        <span class="text-[9px] font-bold uppercase tracking-wider text-slate-400 block">Email Akun</span>
                                        <span class="text-xs font-bold text-slate-700" x-text="currentUser.email"></span>
                                    </div>
                                    <div>
                                        <span class="text-[9px] font-bold uppercase tracking-wider text-slate-400 block">Peran Sistem (Role)</span>
                                        <span class="text-xs font-bold text-brand-green uppercase" x-text="getRoleLabel()"></span>
                                    </div>
                                </div>
                            </div>

                            <!-- Right: Reset Pass Form -->
                            <div class="space-y-4">
                                <h4 class="text-xs font-extrabold text-brand-navy uppercase tracking-wider border-b pb-2">Ganti Kata Sandi</h4>
                                
                                <form onsubmit="handlePassChange(event)" class="space-y-3">
                                    <div>
                                        <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-2">Sandi Lama</label>
                                        <input type="password" required class="block w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-xs text-slate-700 focus:outline-none">
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-2">Sandi Baru</label>
                                        <input type="password" id="new_pass" required class="block w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-xs text-slate-700 focus:outline-none">
                                    </div>
                                    <button type="submit" class="rounded-xl bg-brand-navy px-4 py-2.5 text-xs font-bold text-white shadow-md hover:bg-brand-navy-dark">Update Kata Sandi</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

            </main>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- FLOATING ROLE SWITCHER OVERLAY             -->
    <!-- ========================================== -->
    <div class="fixed bottom-6 right-6 z-50 flex flex-col items-end gap-2">
        <!-- Main switcher toggle button -->
        <button @click="showRoleSwitcher = !showRoleSwitcher" 
            class="h-12 w-12 rounded-full bg-brand-navy text-white shadow-xl shadow-brand-navy/35 flex items-center justify-center text-xl hover:scale-105 hover:bg-brand-navy-dark transition-all border border-white/10 relative">
            <i class="ri-admin-line" x-show="!showRoleSwitcher"></i>
            <i class="ri-close-line" x-show="showRoleSwitcher"></i>
            <span class="absolute -top-1 -left-1 flex h-3 w-3">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-brand-green opacity-75"></span>
                <span class="relative inline-flex rounded-full h-3 w-3 bg-brand-green"></span>
            </span>
        </button>

        <!-- Role options menu -->
        <div x-show="showRoleSwitcher" x-transition 
            class="bg-white rounded-3xl border border-slate-100 shadow-2xl p-4 w-60 space-y-3">
            <div class="border-b pb-2 flex justify-between items-center">
                <span class="text-[9px] font-extrabold uppercase tracking-widest text-slate-400">Pilih Hak Akses Demo</span>
                <span class="text-[10px] font-bold text-brand-green">SIMPPTQ</span>
            </div>
            <div class="space-y-1.5 max-h-[300px] overflow-y-auto pr-1">
                <button @click="switchRole('superadmin')" class="w-full text-left px-3 py-2 text-xs font-bold rounded-xl flex items-center justify-between"
                    :class="currentRole === 'superadmin' ? 'bg-brand-sky text-brand-navy' : 'text-slate-600 hover:bg-slate-50'">
                    <span>Super Admin</span>
                    <i class="ri-check-line" x-show="currentRole === 'superadmin'"></i>
                </button>
                <button @click="switchRole('admin')" class="w-full text-left px-3 py-2 text-xs font-bold rounded-xl flex items-center justify-between"
                    :class="currentRole === 'admin' ? 'bg-brand-sky text-brand-navy' : 'text-slate-600 hover:bg-slate-50'">
                    <span>Admin Operasional</span>
                    <i class="ri-check-line" x-show="currentRole === 'admin'"></i>
                </button>
                <button @click="switchRole('teacher')" class="w-full text-left px-3 py-2 text-xs font-bold rounded-xl flex items-center justify-between"
                    :class="currentRole === 'teacher' ? 'bg-brand-sky text-brand-navy' : 'text-slate-600 hover:bg-slate-50'">
                    <span>Guru (Pengajar)</span>
                    <i class="ri-check-line" x-show="currentRole === 'teacher'"></i>
                </button>
                <button @click="switchRole('staff')" class="w-full text-left px-3 py-2 text-xs font-bold rounded-xl flex items-center justify-between"
                    :class="currentRole === 'staff' ? 'bg-brand-sky text-brand-navy' : 'text-slate-600 hover:bg-slate-50'">
                    <span>Staff Non-Pengajar</span>
                    <i class="ri-check-line" x-show="currentRole === 'staff'"></i>
                </button>
                <button @click="switchRole('hybrid')" class="w-full text-left px-3 py-2 text-xs font-bold rounded-xl flex items-center justify-between"
                    :class="currentRole === 'hybrid' ? 'bg-brand-sky text-brand-navy' : 'text-slate-600 hover:bg-slate-50'">
                    <span>Dua Fungsi</span>
                    <i class="ri-check-line" x-show="currentRole === 'hybrid'"></i>
                </button>
                <button @click="switchRole('leader')" class="w-full text-left px-3 py-2 text-xs font-bold rounded-xl flex items-center justify-between"
                    :class="currentRole === 'leader' ? 'bg-brand-sky text-brand-navy' : 'text-slate-600 hover:bg-slate-50'">
                    <span>Pimpinan</span>
                    <i class="ri-check-line" x-show="currentRole === 'leader'"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- SLIP GAJI PRINT MODAL DETAIL               -->
    <!-- ========================================== -->
    <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 flex items-center justify-center p-4 backdrop-blur-sm"
         x-show="showSlipModal" x-transition>
        <div class="bg-white rounded-3xl shadow-2xl w-full max-w-lg border border-slate-100 overflow-hidden flex flex-col justify-between">
            <!-- Modal Header -->
            <div class="px-6 py-4 bg-brand-navy text-white flex justify-between items-center">
                <span class="text-xs font-bold uppercase tracking-wider">Slip Gaji Bulanan Digital</span>
                <button @click="showSlipModal = false" class="text-white hover:text-brand-sky text-xl"><i class="ri-close-line"></i></button>
            </div>

            <!-- Slip Content (Paper print layout style) -->
            <div class="p-8 space-y-6" id="printable-slip-area">
                <!-- Branding Header inside slip -->
                <div class="flex justify-between items-start border-b-2 border-brand-navy pb-4">
                    <div>
                        <h4 class="text-base font-extrabold text-brand-navy leading-none">PPTQ Nurul Iman</h4>
                        <span class="text-[9px] font-medium tracking-wider text-slate-500 uppercase">Tahfidzul Qur'an</span>
                    </div>
                    <div class="text-right">
                        <span class="text-xs font-bold block text-slate-700">Slip Gaji Karyawan</span>
                        <span class="text-[10px] text-slate-400 font-semibold uppercase">Mei 2026</span>
                    </div>
                </div>

                <!-- Bio Table -->
                <div class="grid grid-cols-2 gap-4 text-xs font-medium text-slate-600">
                    <div>
                        <span class="text-[9px] font-bold uppercase text-slate-400 block">Nama Karyawan</span>
                        <span class="text-brand-navy font-bold" x-text="slipTarget.name"></span>
                    </div>
                    <div>
                        <span class="text-[9px] font-bold uppercase text-slate-400 block">NIK</span>
                        <span x-text="slipTarget.nik"></span>
                    </div>
                    <div>
                        <span class="text-[9px] font-bold uppercase text-slate-400 block">Jabatan / Unit</span>
                        <span x-text="slipTarget.jabatan"></span>
                    </div>
                    <div>
                        <span class="text-[9px] font-bold uppercase text-slate-400 block">Status Hubungan</span>
                        <span x-text="slipTarget.status_kerja"></span>
                    </div>
                </div>

                <!-- Salary items table grid -->
                <div class="space-y-3.5 border-t border-slate-100 pt-4">
                    <h5 class="text-[10px] font-extrabold uppercase text-brand-navy tracking-wider">Rincian Komponen Gaji</h5>
                    
                    <div class="space-y-2 text-xs font-medium">
                        <div class="flex justify-between">
                            <span class="text-slate-500">1. Gaji Pokok (Base)</span>
                            <span class="text-slate-700" x-text="formatRupiah(slipTarget.salary_base)"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">2. Tunjangan Kinerja (Allowance)</span>
                            <span class="text-brand-green font-semibold" x-text="'+ ' + formatRupiah(slipTarget.salary_allowance)"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">3. Potongan Presensi (Deduction)</span>
                            <span class="text-rose-500" x-text="'- ' + formatRupiah(slipTarget.salary_deduction)"></span>
                        </div>
                    </div>
                </div>

                <!-- Final Net salary board (Deloitte Blue ribbon theme) -->
                <div class="rounded-2xl bg-brand-sky border border-brand-sky p-4 flex justify-between items-center text-xs">
                    <span class="font-bold text-brand-navy uppercase tracking-wider">Gaji Bersih Diterima (Net)</span>
                    <span class="text-base font-extrabold text-brand-navy" x-text="formatRupiah(slipTarget.salary_base + slipTarget.salary_allowance - slipTarget.salary_deduction)"></span>
                </div>
            </div>

            <!-- Print Actions -->
            <div class="px-6 py-4 bg-slate-50 border-t flex justify-end gap-3">
                <button @click="showSlipModal = false" class="border border-slate-200 bg-white hover:bg-slate-50 rounded-xl px-4 py-2 text-xs font-semibold text-slate-600">Tutup</button>
                <button @click="triggerPrintSlip()" class="bg-brand-green text-white hover:bg-brand-green-dark rounded-xl px-4 py-2 text-xs font-bold shadow-md shadow-brand-green/20">
                    <i class="ri-printer-line"></i> Cetak / Unduh PDF
                </button>
            </div>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- SANTRI CARD GENERATE PREVIEW MODAL         -->
    <!-- ========================================== -->
    <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 flex items-center justify-center p-4 backdrop-blur-sm"
         x-show="showCardModal" x-transition>
        <div class="bg-white rounded-3xl shadow-2xl w-full max-w-sm border border-slate-100 overflow-hidden flex flex-col justify-between">
            <!-- Modal Header -->
            <div class="px-6 py-4 bg-brand-navy text-white flex justify-between items-center">
                <span class="text-xs font-bold uppercase tracking-wider">Kartu Identitas Santri</span>
                <button @click="showCardModal = false" class="text-white hover:text-brand-sky text-xl"><i class="ri-close-line"></i></button>
            </div>

            <!-- Card layout mirroring modern design aesthetics -->
            <div class="p-6 flex justify-center bg-slate-50">
                <div class="w-80 h-[480px] bg-gradient-to-b from-brand-navy via-[#0d276b] to-brand-navy-dark rounded-3xl p-5 text-white flex flex-col justify-between shadow-xl relative overflow-hidden border border-white/10">
                    <!-- Concentric visual background shapes -->
                    <div class="absolute -top-20 -right-20 h-40 w-40 rounded-full bg-brand-green/25 blur-xl"></div>
                    <div class="absolute -bottom-20 -left-20 h-40 w-40 rounded-full bg-brand-teal/20 blur-xl"></div>

                    <!-- Card Header -->
                    <div class="flex items-center gap-2.5 pb-3 border-b border-white/10 z-10">
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-green text-white font-bold text-sm shadow-sm">
                            NI
                        </div>
                        <div>
                            <span class="text-xs font-bold block leading-none">PPTQ Nurul Iman</span>
                            <span class="text-[8px] uppercase tracking-wider text-brand-green block mt-0.5">Kartu Digital Santri</span>
                        </div>
                    </div>

                    <!-- Photo & Bio -->
                    <div class="flex flex-col items-center space-y-3 z-10 pt-4">
                        <div class="h-28 w-24 rounded-2xl bg-white/10 border-2 border-brand-green/50 flex items-center justify-center text-white/50 text-4xl overflow-hidden shadow-inner bg-slate-800">
                            <i class="ri-user-3-fill"></i>
                        </div>
                        
                        <div class="text-center">
                            <h4 class="text-sm font-extrabold tracking-tight" x-text="cardTarget.name"></h4>
                            <span class="text-[10px] text-brand-sky/80 uppercase font-semibold block mt-0.5" x-text="'Kelas ' + cardTarget.class_name"></span>
                        </div>
                    </div>

                    <!-- Bio detail list -->
                    <div class="grid grid-cols-2 gap-2 text-center text-[9px] font-bold text-brand-sky/60 z-10 bg-white/5 p-2.5 border border-white/5 rounded-xl">
                        <div>
                            <span class="block">NOMOR INDUK (NIS)</span>
                            <span class="text-white text-xs" x-text="cardTarget.nis"></span>
                        </div>
                        <div>
                            <span class="block">STATUS SANTRI</span>
                            <span class="text-brand-green text-xs" x-text="cardTarget.status"></span>
                        </div>
                    </div>

                    <!-- Barcode/QR generator rendering -->
                    <div class="flex flex-col items-center space-y-1.5 z-10 border-t border-white/10 pt-3">
                        <!-- Simulated QR Code container -->
                        <div class="p-2.5 bg-white rounded-xl shadow-md border border-slate-100 flex items-center justify-center h-20 w-20">
                            <!-- Draw simple dynamic block segments to look like a QR code -->
                            <div class="grid grid-cols-5 gap-0.5 h-full w-full">
                                <div class="bg-slate-900 rounded-sm"></div><div class="bg-slate-900 rounded-sm"></div><div class="bg-white"></div><div class="bg-slate-900 rounded-sm"></div><div class="bg-slate-900 rounded-sm"></div>
                                <div class="bg-slate-900 rounded-sm"></div><div class="bg-white"></div><div class="bg-slate-900 rounded-sm"></div><div class="bg-white"></div><div class="bg-slate-900 rounded-sm"></div>
                                <div class="bg-white"></div><div class="bg-slate-900 rounded-sm"></div><div class="bg-slate-900 rounded-sm"></div><div class="bg-white"></div><div class="bg-white"></div>
                                <div class="bg-slate-900 rounded-sm"></div><div class="bg-white"></div><div class="bg-slate-900 rounded-sm"></div><div class="bg-slate-900 rounded-sm"></div><div class="bg-slate-900 rounded-sm"></div>
                                <div class="bg-slate-900 rounded-sm"></div><div class="bg-slate-900 rounded-sm"></div><div class="bg-white"></div><div class="bg-slate-900 rounded-sm"></div><div class="bg-slate-900 rounded-sm"></div>
                            </div>
                        </div>
                        <span class="text-[8px] font-bold text-brand-sky/60 uppercase tracking-widest" x-text="'TOKEN: ' + cardTarget.card_token"></span>
                    </div>
                </div>
            </div>

            <!-- Print Actions -->
            <div class="px-6 py-4 bg-slate-50 border-t flex justify-end gap-3">
                <button @click="showCardModal = false" class="border border-slate-200 bg-white hover:bg-slate-50 rounded-xl px-4 py-2 text-xs font-semibold text-slate-600">Tutup</button>
                <button @click="triggerPrintCard()" class="bg-brand-green text-white hover:bg-brand-green-dark rounded-xl px-4 py-2 text-xs font-bold shadow-md shadow-brand-green/20">
                    <i class="ri-printer-line"></i> Cetak Kartu Fisik
                </button>
            </div>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- SYSTEM APPLICATION DATA & LOGIC            -->
    <!-- ========================================== -->
    <script>
        function simpptqApp() {
            return {
                // Routing and UI panels state
                activeTab: 'dashboard',
                currentRole: 'admin', // fallback
                showRoleSwitcher: false,
                showSlipModal: false,
                showCardModal: false,
                selectedRoleForEdit: 'teacher',

                // WhatsApp Integration State (Fonnte API)
                whatsappToken: 'FONNTE_API_TOKEN_MOCK_123456789',
                whatsappSender: '081234567890',
                whatsappConnected: true,

                // Branding & Landing Page State
                logoType: localStorage.getItem('simpptq_logo_type') || 'text',
                logoText: localStorage.getItem('simpptq_logo_text') || 'NI',
                logoImage: localStorage.getItem('simpptq_logo_image') || '',
                pondokName: localStorage.getItem('simpptq_pondok_name') || 'PPTQ Nurul Iman',
                pondokTagline: localStorage.getItem('simpptq_pondok_tagline') || 'Sistem Manajemen Terpadu',
                landingTitle: localStorage.getItem('simpptq_landing_hero_title') || 'Membangun Generasi',
                landingTitleHighlight: localStorage.getItem('simpptq_landing_hero_title_highlight') || 'Qur\'ani & Unggul',
                landingDesc: localStorage.getItem('simpptq_landing_hero_desc') || 'Selamat datang di Sistem Informasi Manajemen Terpusat PPTQ Nurul Iman. Solusi digital modern untuk mengelola data personil, kehadiran GPS, perkembangan santri, penggajian, dan operasional kepondokan secara real-time.',
                landingHeroImage: localStorage.getItem('simpptq_landing_hero_image') || 'default',
                landingHeroImageCustom: localStorage.getItem('simpptq_landing_hero_image_custom') || '/pondok_hero_banner.png',
                statsPersonnel: localStorage.getItem('simpptq_landing_stats_personnel') || '40+',
                statsSantri: localStorage.getItem('simpptq_landing_stats_santri') || '350+',
                statsHalaqah: localStorage.getItem('simpptq_landing_stats_halaqah') || '15+',
                statsAccuracy: localStorage.getItem('simpptq_landing_stats_accuracy') || '100%',

                // Global Toast Alerts
                toast: {
                    visible: false,
                    type: 'success',
                    message: ''
                },

                // GPS simulation values
                gpsInsideRadius: true,

                // Current Session holder
                currentUser: {
                    name: 'Admin Pondok',
                    email: 'petugas@nuruliman.net'
                },

                // Form state helpers
                behaviorFormType: 'Pelanggaran',

                // Modal structures targets
                slipTarget: { name: '', nik: '', jabatan: '', status_kerja: '', salary_base: 0, salary_allowance: 0, salary_deduction: 0 },
                cardTarget: { name: '', class_name: '', nis: '', status: '', card_token: '' },

                // Filter objects
                filters: {
                    personnel: {
                        fungsi: 'Semua',
                        status: 'Semua'
                    }
                },

                // ----------------------------------------------------
                // DEMO SEED DATA (REPRESENTING DB ENTITIES IN MEMORY)
                // ----------------------------------------------------
                users: [
                    { id: 1, email: 'superadmin@nuruliman.net', role: 'superadmin', personnel_name: 'Dr. Zeth Boroh, Sp.KO', active: true },
                    { id: 2, email: 'petugas@nuruliman.net', role: 'admin', personnel_name: 'Admin Pondok', active: true },
                    { id: 3, email: 'ustadz.ahmad@nuruliman.net', role: 'teacher', personnel_name: 'Ustadz Ahmad Fauzi', active: true },
                    { id: 4, email: 'staff.budiyono@nuruliman.net', role: 'staff', personnel_name: 'Budiyono, S.Kom', active: true },
                    { id: 5, email: 'ustadz.fatkur@nuruliman.net', role: 'hybrid', personnel_name: 'Ustadz Fatkur Rahman', active: true },
                    { id: 6, email: 'pimpinan.kiai@nuruliman.net', role: 'leader', personnel_name: 'K.H. Nurul Huda', active: true }
                ],

                roles: [
                    { name: 'superadmin', label: 'Super Admin', permissions: ['dashboard_view', 'user_manage', 'role_manage', 'personnel_manage'] },
                    { name: 'admin', label: 'Admin Operasional', permissions: ['dashboard_view', 'personnel_view', 'schedule_view', 'presence_gps', 'leave_apply', 'leave_approve', 'swap_apply', 'swap_approve', 'payroll_view', 'santri_view', 'class_view', 'santri_presence', 'behavior_log', 'grade_log', 'visit_log', 'reports_view'] },
                    { name: 'teacher', label: 'Guru (Pengajar)', permissions: ['dashboard_view', 'schedule_view', 'presence_gps', 'leave_apply', 'swap_apply', 'santri_view', 'grade_log', 'behavior_log'] },
                    { name: 'staff', label: 'Staff Non-Pengajar', permissions: ['dashboard_view', 'presence_gps', 'leave_apply'] },
                    { name: 'hybrid', label: 'Dua Fungsi', permissions: ['dashboard_view', 'schedule_view', 'presence_gps', 'leave_apply', 'swap_apply', 'santri_view', 'grade_log'] },
                    { name: 'leader', label: 'Pimpinan', permissions: ['dashboard_view', 'reports_view', 'leave_approve', 'swap_approve'] }
                ],

                availablePermissions: [
                    { key: 'dashboard_view', label: 'Lihat Dashboard', desc: 'Mengizinkan user melihat ringkasan dashboard.' },
                    { key: 'user_manage', label: 'Kelola User Akun', desc: 'Hak CRUD akun login operator.' },
                    { key: 'role_manage', label: 'Kelola Role/Permission', desc: 'Konfigurasi granular checkbox hak akses.' },
                    { key: 'personnel_manage', label: 'Kelola Data Karyawan', desc: 'Input, hapus, dan verifikasi berkas SDM.' },
                    { key: 'presence_gps', label: 'Presensi GPS Web', desc: 'Mengizinkan check-in tervalidasi radius GPS.' },
                    { key: 'leave_apply', label: 'Pengajuan Izin Cuti', desc: 'Kirim formulir izin sakit / keperluan dinas.' },
                    { key: 'leave_approve', label: 'Persetujuan Izin', desc: 'Operator berhak menyetujui ajuan izin.' },
                    { key: 'swap_apply', label: 'Ajukan Tukar Jadwal', desc: 'Melakukan klaim tukar jam mengajar.' },
                    { key: 'swap_approve', label: 'Persetujuan Tukar', desc: 'Verifikasi bentrok dan setujui pertukaran.' },
                    { key: 'payroll_view', label: 'Penggajian & Slip', desc: 'Lihat ringkasan payroll pondok & cetak slip.' },
                    { key: 'santri_view', label: 'Data Induk Santri', desc: 'Akses profil, walikelas, dan cetak kartu.' },
                    { key: 'santri_presence', label: 'Absensi Scan Santri', desc: 'Operasikan mesin simulator barcode absensi.' },
                    { key: 'behavior_log', label: 'Poin Perilaku Santri', desc: 'Catat pelanggaran disiplin dan prestasi.' },
                    { key: 'grade_log', label: 'Nilai & Perkembangan', desc: 'Input nilai halaqah & hafalan Al-Qur\'an.' },
                    { key: 'visit_log', label: 'Kunjungan Jenguk', desc: 'Catat nama pengunjung walisantri.' },
                    { key: 'reports_view', label: 'Laporan Strategis', desc: 'Unduh rekapitulasi data & cetak laporan eksekutif.' }
                ],

                personnel: [
                    { id: 1, name: 'Dr. Zeth Boroh, Sp.KO', nik: '3404100204910001', email: 'zeth@nuruliman.net', phone: '081234567890', jabatan: 'Dokter Kesehatan Pondok', status_kerja: 'Tetap', fungsi: 'Non-Pengajar', salary_base: 4500000, salary_allowance: 750000, salary_deduction: 50000, documents: ['KTP.pdf', 'Ijazah.pdf'] },
                    { id: 2, name: 'Ustadz Ahmad Fauzi', nik: '3404100204910002', email: 'ahmad.fauzi@nuruliman.net', phone: '081298765432', jabatan: 'Wali Asrama Takhassus', status_kerja: 'Tetap', fungsi: 'Pengajar', salary_base: 3200000, salary_allowance: 500000, salary_deduction: 0, documents: ['KTP.pdf', 'Sertifikat_Tahfidz.pdf'] },
                    { id: 3, name: 'Budiyono, S.Kom', nik: '3404100204910003', email: 'budiyono@nuruliman.net', phone: '081345678912', jabatan: 'Staff Administrasi Akademik', status_kerja: 'Tidak Tetap', fungsi: 'Non-Pengajar', salary_base: 2800000, salary_allowance: 300000, salary_deduction: 150000, documents: ['KTP.pdf'] },
                    { id: 4, name: 'Ustadz Fatkur Rahman', nik: '3404100204910004', email: 'fatkur@nuruliman.net', phone: '081398765411', jabatan: 'Pengajar Nahwu & Staff', status_kerja: 'Tetap', fungsi: 'Dua Fungsi', salary_base: 3800000, salary_allowance: 600000, salary_deduction: 0, documents: ['KTP.pdf', 'Ijazah_S1.pdf'] }
                ],

                schedules: [
                    { id: 1, teacher_name: 'Ustadz Ahmad Fauzi', subject: 'Tahfidzul Qur\'an Sesi Subuh', class_name: '7A', day: 'Senin', time: '05:00 - 06:00' },
                    { id: 2, teacher_name: 'Ustadz Ahmad Fauzi', subject: 'Tahfidzul Qur\'an Sesi Sore', class_name: '7A', day: 'Selasa', time: '16:00 - 17:30' },
                    { id: 3, teacher_name: 'Ustadz Fatkur Rahman', subject: 'Nahwu Sharaf', class_name: '8B', day: 'Rabu', time: '08:00 - 09:30' },
                    { id: 4, teacher_name: 'Ustadz Fatkur Rahman', subject: 'Hadits Arbain', class_name: '7A', day: 'Kamis', time: '10:00 - 11:30' }
                ],

                presenceLogs: [
                    { id: 1, name: 'Ustadz Ahmad Fauzi', date: '04 Jun 2026', check_in: '04:45', check_out: '17:40', location: 'Asrama Pondok', status: 'Tepat Waktu' },
                    { id: 2, name: 'Budiyono, S.Kom', date: '04 Jun 2026', check_in: '08:15', check_out: '16:00', location: 'Kantor Sekretariat', status: 'Terlambat' }
                ],

                leaveRequests: [
                    { id: 1, name: 'Budiyono, S.Kom', type: 'Cuti Tahunan', start: '2026-06-08', end: '2026-06-10', reason: 'Acara keluarga pernikahan adik kandung', status: 'Diajukan' },
                    { id: 2, name: 'Ustadz Ahmad Fauzi', type: 'Sakit', start: '2026-06-02', end: '2026-06-03', reason: 'Demam tinggi butuh bedrest (surat dokter terlampir)', status: 'Disetujui' }
                ],

                swapRequests: [
                    { id: 1, teacher: 'Ustadz Ahmad Fauzi', schedule_info: 'Selasa (16:00) - Tahfidz', date: '2026-06-09', target_teacher: 'Ustadz Fatkur Rahman', status: 'Diajukan' }
                ],

                santri: [
                    { id: 1, name: 'Muhammad Hafizh Al-Fatih', nis: '26001', nisn: '0098765431', class_name: '7A', wali: 'Rahmat Kartolo', status: 'Aktif', card_token: 'TOKEN_HAFIZH_992' },
                    { id: 2, name: 'Ahmad Rafli Aditya', nis: '26002', nisn: '0098765432', class_name: '7A', wali: 'Sugeng Pranoto', status: 'Aktif', card_token: 'TOKEN_RAFLI_554' },
                    { id: 3, name: 'dr. Ika Safira (Alumni)', nis: '24003', nisn: '0078654321', class_name: 'Lulus', wali: 'Mahmudin', status: 'Lulus', card_token: 'TOKEN_IKA_881' }
                ],

                classes: [
                    { name: '7A', grade: 7, walikelas: 'Ustadz Ahmad Fauzi' },
                    { name: '8B', grade: 8, walikelas: 'Ustadz Fatkur Rahman' }
                ],

                santriPresence: [
                    { id: 1, date: '04 Jun 2026', time: '04:55', name: 'Muhammad Hafizh Al-Fatih', class_name: '7A' },
                    { id: 2, date: '04 Jun 2026', time: '05:01', name: 'Ahmad Rafli Aditya', class_name: '7A' }
                ],

                behaviors: [
                    { id: 1, name: 'Muhammad Hafizh Al-Fatih', date: '03 Jun 2026', type: 'Kebaikan', points: 15, note: 'Membantu merapikan perpustakaan asrama tanpa disuruh.' },
                    { id: 2, name: 'Ahmad Rafli Aditya', date: '02 Jun 2026', type: 'Pelanggaran', points: 10, note: 'Terlambat bangun subuh dan tidak mengikuti jamaah masjid.' }
                ],

                grades: [
                    { id: 1, name: 'Muhammad Hafizh Al-Fatih', date: '01 Jun 2026', subject: 'Tahfidz Al-Qur\'an', score: 92, note: 'Lancar setoran Juz 29 dengan makhraj fasih.' }
                ],

                visits: [
                    { id: 1, name: 'Muhammad Hafizh Al-Fatih', date: '04 Jun 2026 10:15', visitor: 'Rahmat Kartolo', relation: 'Ayah Kandung', note: 'Mengantar bekal bulanan dan pakaian ganti.' }
                ],

                announcements: [
                    { id: 1, title: 'Pembagian Raport Bulanan Santri', target: 'Semua', date: '04 Jun 2026', content: 'Laporan perkembangan hafalan dan akhlak dibagikan mulai akhir pekan ini.', active: true },
                    { id: 2, title: 'Rapat Koordinasi Evaluasi Jadwal Master', target: 'Guru', date: '02 Jun 2026', content: 'Diwajibkan berkumpul di sadewa room jam 13:00 selesai shalat dzuhur.', active: true }
                ],

                // Initialize App
                init() {
                    // Read role from query param to enable instant preview links from login page
                    const urlParams = new URLSearchParams(window.location.search);
                    const roleParam = urlParams.get('role');
                    if (roleParam) {
                        this.currentRole = roleParam;
                    }
                    this.syncSessionUser();
                    
                    // Setup Chart after browser renders
                    this.$nextTick(() => {
                        this.renderChart();
                    });
                },

                // ----------------------------------------------------
                // ACCESS CONTROL MATRIX UTILITIES
                // ----------------------------------------------------
                hasAccess(permissionKey) {
                    const roleConfig = this.roles.find(r => r.name === this.currentRole);
                    if (!roleConfig) return false;
                    return roleConfig.permissions.includes(permissionKey);
                },

                getRoleLabel() {
                    const roleConfig = this.roles.find(r => r.name === this.currentRole);
                    return roleConfig ? roleConfig.label : 'Guest';
                },

                getRoleLabelOf(roleName) {
                    const roleConfig = this.roles.find(r => r.name === roleName);
                    return roleConfig ? roleConfig.label : 'Guest';
                },

                hasRolePermission(roleName, permKey) {
                    const r = this.roles.find(ro => ro.name === roleName);
                    return r ? r.permissions.includes(permKey) : false;
                },

                toggleRolePermission(roleName, permKey) {
                    const r = this.roles.find(ro => ro.name === roleName);
                    if (!r) return;
                    
                    const index = r.permissions.indexOf(permKey);
                    if (index > -1) {
                        r.permissions.splice(index, 1);
                    } else {
                        r.permissions.push(permKey);
                    }
                    this.triggerToast('success', 'Hak akses peran ' + r.label + ' berhasil diperbarui.');
                },

                // Sync current simulated session user profile
                syncSessionUser() {
                    const userDb = this.users.find(u => u.role === this.currentRole);
                    if (userDb) {
                        this.currentUser.name = userDb.personnel_name;
                        this.currentUser.email = userDb.email;
                    }
                },

                currentUserInitials() {
                    return this.currentUser.name.split(' ').map(n => n[0]).join('').substring(0,2).toUpperCase();
                },

                switchRole(newRole) {
                    this.currentRole = newRole;
                    this.syncSessionUser();
                    this.showRoleSwitcher = false;
                    this.triggerToast('success', 'Berhasil beralih hak akses peran: ' + this.getRoleLabel());
                    
                    // Reset to dashboard when role switches to prevent UI lockouts
                    this.activeTab = 'dashboard';

                    // Re-render chart if leader is loaded
                    if (newRole === 'leader') {
                        setTimeout(() => {
                            this.renderChart();
                        }, 100);
                    }
                },

                // Toast Helper
                triggerToast(type, msg) {
                    this.toast.type = type;
                    this.toast.message = msg;
                    this.toast.visible = true;
                    setTimeout(() => {
                        this.toast.visible = false;
                    }, 4000);
                },

                // Format Indonesian Date
                getFormattedDate() {
                    const date = new Date();
                    const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
                    const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
                    return days[date.getDay()] + ', ' + date.getDate() + ' ' + months[date.getMonth()] + ' ' + date.getFullYear();
                },

                getTabTitle() {
                    const titles = {
                        dashboard: 'Dashboard Utama',
                        users: 'Manajemen Akun Login',
                        roles: 'Pengaturan Peran & Izin',
                        'personnel-directory': 'Manajemen Data Personil',
                        schedule: 'Jadwal Mengajar Asatidzah',
                        'presence-gps': 'Laporan Presensi GPS',
                        leaves: 'Pengajuan Izin / Cuti Karyawan',
                        swaps: 'Tukar Jam Mengajar',
                        payroll: 'Daftar Penggajian & Slip Gaji',
                        santri: 'Direktori Data Santri',
                        classes: 'Manajemen Rombel Kelas',
                        'presence-santri': 'Scan QR Absensi Santri',
                        behaviors: 'Pencatatan Poin & Perilaku',
                        grades: 'Evaluasi Perkembangan',
                        visits: 'Register Kunjungan Wali',
                        announcements: 'Papan Pengumuman',
                        reports: 'Laporan Eksekutif',
                        profile: 'Akun Kredensial Saya',
                        whatsapp: 'Integrasi WhatsApp Fonnte',
                        branding: 'Pengaturan Branding & Landing Page'
                    };
                    return titles[this.activeTab] || 'Sistem Informasi';
                },

                formatRupiah(num) {
                    return 'Rp ' + Number(num).toLocaleString('id-ID');
                },

                // ----------------------------------------------------
                // SIMULATOR HANDLERS
                // ----------------------------------------------------
                performCheckIn() {
                    if (!this.gpsInsideRadius) {
                        this.triggerToast('warning', 'Presensi Ditolak! Koordinat GPS Anda di luar radius aman 100 meter.');
                        return;
                    }

                    // Check if already checked in
                    const checkLog = this.presenceLogs.find(p => p.name === this.currentUser.name && p.date === '04 Jun 2026');
                    if (checkLog && checkLog.check_in) {
                        this.triggerToast('warning', 'Presensi Ditolak! Anda sudah tercatat Check-In hari ini.');
                        return;
                    }

                    const time = new Date().toTimeString().split(' ')[0].substring(0, 5);
                    this.presenceLogs.unshift({
                        id: this.presenceLogs.length + 1,
                        name: this.currentUser.name,
                        date: '04 Jun 2026',
                        check_in: time,
                        check_out: '',
                        location: 'Masjid Utama Pondok',
                        status: 'Tepat Waktu'
                    });
                    this.triggerToast('success', 'Presensi Check-In Berhasil dicatat pada jam ' + time);
                },

                performCheckOut() {
                    const checkLog = this.presenceLogs.find(p => p.name === this.currentUser.name && p.date === '04 Jun 2026');
                    if (!checkLog) {
                        this.triggerToast('warning', 'Presensi Ditolak! Anda belum melakukan Check-In hari ini.');
                        return;
                    }
                    if (checkLog.check_out) {
                        this.triggerToast('warning', 'Presensi Ditolak! Anda sudah melakukan Check-Out hari ini.');
                        return;
                    }

                    const time = new Date().toTimeString().split(' ')[0].substring(0, 5);
                    checkLog.check_out = time;
                    this.triggerToast('success', 'Presensi Check-Out Berhasil dicatat pada jam ' + time);
                },

                // ----------------------------------------------------
                // LEAVE AND WORKFLOW REQUESTS LOGIC
                // ----------------------------------------------------
                getPendingLeavesCount() {
                    return this.leaveRequests.filter(l => l.status === 'Diajukan').length;
                },

                approveLeave(id) {
                    const req = this.leaveRequests.find(l => l.id === id);
                    if (req) {
                        req.status = 'Disetujui';
                        this.triggerToast('success', 'Izin ' + req.name + ' berhasil disetujui.');
                    }
                },

                rejectLeave(id) {
                    const req = this.leaveRequests.find(l => l.id === id);
                    if (req) {
                        req.status = 'Ditolak';
                        this.triggerToast('warning', 'Izin ' + req.name + ' ditolak.');
                    }
                },

                handleLeaveForm(e) {
                    e.preventDefault();
                    const type = document.getElementById('leave_type').value;
                    const start = document.getElementById('leave_start').value;
                    const end = document.getElementById('leave_end').value;
                    const reason = document.getElementById('leave_reason').value;

                    this.leaveRequests.unshift({
                        id: this.leaveRequests.length + 1,
                        name: this.currentUser.name,
                        type: type,
                        start: start,
                        end: end,
                        reason: reason,
                        status: 'Diajukan'
                    });

                    this.triggerToast('success', 'Form Pengajuan Izin dikirim. Menunggu persetujuan admin.');
                    e.target.reset();
                },

                // ----------------------------------------------------
                // CLASS SWAPS LOGIC
                // ----------------------------------------------------
                getMySchedules() {
                    return this.schedules.filter(s => s.teacher_name === this.currentUser.name);
                },

                getSchedulesForDay(dayName) {
                    return this.schedules.filter(s => s.day === dayName);
                },

                getPendingSwapsCount() {
                    return this.swapRequests.filter(s => s.status === 'Diajukan').length;
                },

                approveSwap(id) {
                    const req = this.swapRequests.find(s => s.id === id);
                    if (req) {
                        req.status = 'Disetujui';
                        this.triggerToast('success', 'Tukar jam mengajar ' + req.teacher + ' disetujui.');
                    }
                },

                rejectSwap(id) {
                    const req = this.swapRequests.find(s => s.id === id);
                    if (req) {
                        req.status = 'Ditolak';
                        this.triggerToast('warning', 'Tukar jam mengajar ' + req.teacher + ' ditolak.');
                    }
                },

                handleSwapForm(e) {
                    e.preventDefault();
                    const schedId = document.getElementById('swap_schedule').value;
                    const schedObj = this.schedules.find(s => s.id == schedId);
                    const date = document.getElementById('swap_date').value;
                    const target = document.getElementById('swap_target').value;
                    const reason = document.getElementById('swap_reason').value;

                    this.swapRequests.unshift({
                        id: this.swapRequests.length + 1,
                        teacher: this.currentUser.name,
                        schedule_info: schedObj.day + ' (' + schedObj.time + ') - ' + schedObj.subject,
                        date: date,
                        target_teacher: target,
                        status: 'Diajukan'
                    });

                    this.triggerToast('success', 'Form Pengajuan Tukar Jam dikirim. Menunggu persetujuan admin.');
                    e.target.reset();
                },

                // ----------------------------------------------------
                // PAYROLL MODAL CONTROLLER
                // ----------------------------------------------------
                openSlipGaji(pObj) {
                    this.slipTarget = { ...pObj };
                    this.showSlipModal = true;
                },

                triggerPrintSlip() {
                    window.print();
                    this.triggerToast('success', 'Slip Gaji berhasil dicetak.');
                    this.showSlipModal = false;
                },

                // ----------------------------------------------------
                // SANTRI CARD GENERATOR CONTROLLER
                // ----------------------------------------------------
                generateSantriCard(sObj) {
                    this.cardTarget = { ...sObj };
                    this.showCardModal = true;
                },

                triggerPrintCard() {
                    this.triggerToast('success', 'File kartu santri dikirim ke antrean cetak printer thermal.');
                    this.showCardModal = false;
                },

                // ----------------------------------------------------
                // SCAN SIMULATOR CONTROLLER
                // ----------------------------------------------------
                triggerSantriScan() {
                    const selId = document.getElementById('scan_santri_select').value;
                    const sObj = this.santri.find(s => s.id == selId);
                    
                    if (sObj) {
                        const time = new Date().toTimeString().split(' ')[0].substring(0, 5);
                        this.santriPresence.unshift({
                            id: this.santriPresence.length + 1,
                            date: '04 Jun 2026',
                            time: time,
                            name: sObj.name,
                            class_name: sObj.class_name
                        });
                        this.triggerToast('success', 'Tembakan Barcode Sukses! Santri ' + sObj.name + ' terabsen Hadir.');
                    }
                },

                // ----------------------------------------------------
                // BEHAVIOR POIN LOGS
                // ----------------------------------------------------
                handleBehaviorForm(e) {
                    e.preventDefault();
                    const name = document.getElementById('behavior_santri').value;
                    const points = parseInt(document.getElementById('behavior_points').value);
                    const note = document.getElementById('behavior_note').value;

                    this.behaviors.unshift({
                        id: this.behaviors.length + 1,
                        name: name,
                        date: '04 Jun 2026',
                        type: this.behaviorFormType,
                        points: points,
                        note: note
                    });

                    this.triggerToast('success', 'Log Poin Perilaku berhasil disimpan.');
                    e.target.reset();
                },

                // ----------------------------------------------------
                // GRADEBOOK
                // ----------------------------------------------------
                handleGradeForm(e) {
                    e.preventDefault();
                    const name = document.getElementById('grade_santri').value;
                    const subject = document.getElementById('grade_subject').value;
                    const score = parseInt(document.getElementById('grade_score').value);
                    const note = document.getElementById('grade_note').value;

                    this.grades.unshift({
                        id: this.grades.length + 1,
                        name: name,
                        date: '04 Jun 2026',
                        subject: subject,
                        score: score,
                        note: note
                    });

                    this.triggerToast('success', 'Nilai perkembangan santri disimpan.');
                    e.target.reset();
                },

                // ----------------------------------------------------
                // VISITORS
                // ----------------------------------------------------
                handleVisitForm(e) {
                    e.preventDefault();
                    const name = document.getElementById('visit_santri').value;
                    const visitor = document.getElementById('visit_name').value;
                    const relation = document.getElementById('visit_relation').value;
                    const note = document.getElementById('visit_note').value;

                    this.visits.unshift({
                        id: this.visits.length + 1,
                        name: name,
                        date: '04 Jun 2026 13:45',
                        visitor: visitor,
                        relation: relation,
                        note: note
                    });

                    this.triggerToast('success', 'Register kunjungan jenguk disimpan.');
                    e.target.reset();
                },

                // ----------------------------------------------------
                // ANNOUNCEMENTS
                // ----------------------------------------------------
                handleAnnouncementForm(e) {
                    e.preventDefault();
                    const title = document.getElementById('ann_title').value;
                    const target = document.getElementById('ann_target').value;
                    const date = document.getElementById('ann_date').value;
                    const content = document.getElementById('ann_content').value;

                    this.announcements.unshift({
                        id: this.announcements.length + 1,
                        title: title,
                        target: target,
                        date: date,
                        content: content,
                        active: true
                    });

                    this.triggerToast('success', 'Pengumuman baru disiarkan.');
                    e.target.reset();
                },

                toggleAnnActive(id) {
                    const a = this.announcements.find(ann => ann.id === id);
                    if (a) {
                        a.active = !a.active;
                        this.triggerToast('success', 'Status pengumuman berhasil diubah.');
                    }
                },

                // ----------------------------------------------------
                // USER MANAGEMENT ACTION
                // ----------------------------------------------------
                toggleUserActive(id) {
                    const u = this.users.find(us => us.id === id);
                    if (u) {
                        u.active = !u.active;
                        this.triggerToast('success', 'Status akun ' + u.email + ' diubah.');
                    }
                },

                resetUserPassword(id) {
                    const u = this.users.find(us => us.id === id);
                    if (u) {
                        this.triggerToast('success', 'Password akun ' + u.email + ' berhasil di-reset ke default.');
                    }
                },

                openCreateUserModal() {
                    this.triggerToast('warning', 'Fitur Akun Baru memerlukan sinkronisasi data master. Isi detail demo di daftar personil.');
                },

                // ----------------------------------------------------
                // PERSONNEL MANAGEMENT ACTION
                // ----------------------------------------------------
                getFilteredPersonnel() {
                    return this.personnel.filter(p => {
                        const checkFungsi = this.filters.personnel.fungsi === 'Semua' || p.fungsi === this.filters.personnel.fungsi;
                        const checkStatus = this.filters.personnel.status === 'Semua' || p.status_kerja === this.filters.personnel.status;
                        return checkFungsi && checkStatus;
                    });
                },

                openCreatePersonnelModal() {
                    const name = prompt('Masukkan Nama Lengkap Karyawan Baru:');
                    if (!name) return;
                    const nik = prompt('Masukkan NIK (16 digit):', '340410020491' + Math.floor(1000 + Math.random() * 9000));
                    const jabatan = prompt('Masukkan Jabatan / Unit Kerja:', 'Staff');
                    const base_salary = parseInt(prompt('Masukkan Gaji Pokok (base):', '2500000'));

                    this.personnel.push({
                        id: this.personnel.length + 1,
                        name: name,
                        nik: nik,
                        email: name.toLowerCase().replace(' ', '.') + '@nuruliman.net',
                        phone: '0812' + Math.floor(10000000 + Math.random() * 90000000),
                        jabatan: jabatan,
                        status_kerja: 'Tidak Tetap',
                        fungsi: 'Non-Pengajar',
                        salary_base: base_salary,
                        salary_allowance: 250000,
                        salary_deduction: 0,
                        documents: ['KTP.pdf']
                    });

                    this.triggerToast('success', 'Data personil baru ' + name + ' ditambahkan ke database.');
                },

                openEditPersonnelModal(pObj) {
                    const newName = prompt('Edit Nama Lengkap:', pObj.name);
                    if (newName) {
                        pObj.name = newName;
                        this.triggerToast('success', 'Data personil berhasil diperbarui.');
                    }
                },

                deletePersonnel(id) {
                    if (confirm('Apakah Anda yakin ingin menghapus data personil ini?')) {
                        this.personnel = this.personnel.filter(p => p.id !== id);
                        this.triggerToast('warning', 'Data personil dihapus dari database.');
                    }
                },

                openCreateSantriModal() {
                    const name = prompt('Nama Lengkap Santri Baru:');
                    if (!name) return;
                    const nis = prompt('Nomor Induk Santri (NIS):', '260' + Math.floor(10 + Math.random() * 89));
                    const className = prompt('Masukkan Rombel Kelas:', '7A');

                    this.santri.push({
                        id: this.santri.length + 1,
                        name: name,
                        nis: nis,
                        nisn: '0098' + Math.floor(100000 + Math.random() * 900000),
                        class_name: className,
                        wali: 'Wali Santri Baru',
                        status: 'Aktif',
                        card_token: 'TOKEN_' + name.substring(0, 4).toUpperCase() + '_' + Math.floor(100 + Math.random() * 900)
                    });
                    this.triggerToast('success', 'Registrasi santri ' + name + ' berhasil disimpan.');
                },

                handlePassChange(e) {
                    e.preventDefault();
                    this.triggerToast('success', 'Sandi berhasil diperbarui.');
                    e.target.reset();
                },

                testWhatsappConnection() {
                    if (!this.whatsappToken) {
                        this.triggerToast('warning', 'Koneksi gagal! Silakan masukkan Fonnte API Token terlebih dahulu.');
                        this.whatsappConnected = false;
                        return;
                    }
                    this.whatsappConnected = !this.whatsappConnected;
                    if (this.whatsappConnected) {
                        this.triggerToast('success', 'Koneksi Fonnte API Terhubung! Status device: ACTIVE.');
                    } else {
                        this.triggerToast('warning', 'Koneksi Terputus! Device dinonaktifkan.');
                    }
                },

                sendTestWhatsappMessage(e) {
                    e.preventDefault();
                    const phone = document.getElementById('wa_test_phone').value;
                    const message = document.getElementById('wa_test_message').value;

                    if (!this.whatsappToken) {
                        this.triggerToast('warning', 'Gagal mengirim! Fonnte API Token kosong.');
                        return;
                    }
                    if (!this.whatsappConnected) {
                        this.triggerToast('warning', 'Gagal mengirim! Device WhatsApp berstatus TERPUTUS.');
                        return;
                    }

                    this.triggerToast('success', 'Pesan uji coba sukses dikirim ke ' + phone + ' via Fonnte Gateway API!');
                    e.target.reset();
                },

                saveBrandingSettings() {
                    localStorage.setItem('simpptq_logo_type', this.logoType);
                    localStorage.setItem('simpptq_logo_text', this.logoText);
                    localStorage.setItem('simpptq_logo_image', this.logoImage);
                    localStorage.setItem('simpptq_pondok_name', this.pondokName);
                    localStorage.setItem('simpptq_pondok_tagline', this.pondokTagline);
                    localStorage.setItem('simpptq_landing_hero_title', this.landingTitle);
                    localStorage.setItem('simpptq_landing_hero_title_highlight', this.landingTitleHighlight);
                    localStorage.setItem('simpptq_landing_hero_desc', this.landingDesc);
                    localStorage.setItem('simpptq_landing_hero_image', this.landingHeroImage);
                    localStorage.setItem('simpptq_landing_hero_image_custom', this.landingHeroImageCustom);
                    localStorage.setItem('simpptq_landing_stats_personnel', this.statsPersonnel);
                    localStorage.setItem('simpptq_landing_stats_santri', this.statsSantri);
                    localStorage.setItem('simpptq_landing_stats_halaqah', this.statsHalaqah);
                    localStorage.setItem('simpptq_landing_stats_accuracy', this.statsAccuracy);
                    this.triggerToast('success', 'Pengaturan branding & landing page berhasil disimpan dan dipublikasikan!');
                },

                uploadLogoFile(event) {
                    const file = event.target.files[0];
                    if (!file) return;
                    
                    if (file.size > 500 * 1024) {
                        this.triggerToast('warning', 'Gagal: Ukuran logo tidak boleh melebihi 500 KB!');
                        event.target.value = '';
                        return;
                    }
                    
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        this.logoImage = e.target.result;
                        this.triggerToast('success', 'Logo berhasil diunggah secara lokal (belum disimpan). Silakan klik "Simpan & Publikasikan".');
                    };
                    reader.readAsDataURL(file);
                },

                uploadHeroFile(event) {
                    const file = event.target.files[0];
                    if (!file) return;
                    
                    if (file.size > 1.5 * 1024 * 1024) {
                        this.triggerToast('warning', 'Gagal: Ukuran banner hero tidak boleh melebihi 1.5 MB!');
                        event.target.value = '';
                        return;
                    }
                    
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        this.landingHeroImageCustom = e.target.result;
                        this.triggerToast('success', 'Gambar banner hero berhasil diunggah secara lokal (belum disimpan). Silakan klik "Simpan & Publikasikan".');
                    };
                    reader.readAsDataURL(file);
                },

                // ----------------------------------------------------
                // EXECUTIVE EXECUTIVE CHARTS (Leader Dashboard)
                // ----------------------------------------------------
                renderChart() {
                    const ctx = document.getElementById('leaderDashboardChart');
                    if (!ctx) return;
                    
                    // Clear existing chart instances
                    if (window.leaderChartInstance) {
                        window.leaderChartInstance.destroy();
                    }

                    window.leaderChartInstance = new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'],
                            datasets: [{
                                label: 'Rerata Kehadiran GPS Staff (%)',
                                data: [98, 95, 96, 92, 97],
                                borderColor: '#107c41',
                                backgroundColor: 'rgba(16, 124, 65, 0.05)',
                                borderWidth: 3,
                                fill: true,
                                tension: 0.3
                            }, {
                                label: 'Kehadiran Santri Scan (%)',
                                data: [99, 99, 98, 97, 96],
                                borderColor: '#0b2265',
                                backgroundColor: 'rgba(11, 34, 101, 0.03)',
                                borderWidth: 3,
                                fill: true,
                                tension: 0.3
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    labels: {
                                        font: {
                                            family: 'Plus Jakarta Sans',
                                            weight: 'bold',
                                            size: 10
                                        }
                                    }
                                }
                            },
                            scales: {
                                y: {
                                    min: 80,
                                    max: 100,
                                    ticks: {
                                        font: {
                                            family: 'Plus Jakarta Sans',
                                            size: 9
                                        }
                                    }
                                },
                                x: {
                                    ticks: {
                                        font: {
                                            family: 'Plus Jakarta Sans',
                                            size: 9
                                        }
                                    }
                                }
                            }
                        }
                    });
                }
            };
        }
    </script>

</body>
</html>
