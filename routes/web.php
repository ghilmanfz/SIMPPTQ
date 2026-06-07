<?php

use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BehaviorController;
use App\Http\Controllers\BrandingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GradeController;
use App\Http\Controllers\JadwalController;
use App\Http\Controllers\KelasController;
use App\Http\Controllers\LeaveRequestController;
use App\Http\Controllers\LokasiPresensiController;
use App\Http\Controllers\MapelController;
use App\Http\Controllers\PayrollController;
use App\Http\Controllers\PersonilController;
use App\Http\Controllers\PresensiPersonilController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SantriController;
use App\Http\Controllers\SantriPresenceController;
use App\Http\Controllers\SesiController;
use App\Http\Controllers\SwapRequestController;
use App\Http\Controllers\TahunAjaranController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VisitController;
use App\Http\Controllers\WhatsappController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Halaman Publik
|--------------------------------------------------------------------------
*/
Route::get('/', [DashboardController::class, 'landing'])->name('landing');

/*
|--------------------------------------------------------------------------
| Autentikasi
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

/*
|--------------------------------------------------------------------------
| Aplikasi (perlu login)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->prefix('app')->name('app.')->group(function () {

    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // --- Profil & ganti password (selalu tersedia) ---
    Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

    // --- Pengumuman ---
    Route::get('announcements', [AnnouncementController::class, 'index'])->name('announcements.index')->middleware('permission:announcement_view');
    Route::middleware('permission:announcement_manage')->group(function () {
        Route::post('announcements', [AnnouncementController::class, 'store'])->name('announcements.store');
        Route::put('announcements/{announcement}', [AnnouncementController::class, 'update'])->name('announcements.update');
        Route::delete('announcements/{announcement}', [AnnouncementController::class, 'destroy'])->name('announcements.destroy');
    });

    // --- Presensi personil (GPS) ---
    Route::middleware('permission:presence_gps')->group(function () {
        Route::get('presensi', [PresensiPersonilController::class, 'index'])->name('presensi.index');
        Route::post('presensi/check-in', [PresensiPersonilController::class, 'checkIn'])->name('presensi.checkin');
        Route::post('presensi/check-out', [PresensiPersonilController::class, 'checkOut'])->name('presensi.checkout');
    });
    Route::get('presensi/rekap', [PresensiPersonilController::class, 'rekap'])->name('presensi.rekap')->middleware('permission:presence_manage');
    Route::get('presensi/rekap/export', [PresensiPersonilController::class, 'exportRekap'])->name('presensi.rekap.export')->middleware('permission:presence_manage');

    // --- Lokasi presensi ---
    Route::resource('lokasi', LokasiPresensiController::class)->except(['show', 'create', 'edit'])->middleware('permission:location_manage');

    // --- Jadwal mengajar ---
    Route::get('jadwal', [JadwalController::class, 'index'])->name('jadwal.index')->middleware('permission:schedule_view');
    Route::middleware('permission:schedule_manage')->group(function () {
        Route::post('jadwal', [JadwalController::class, 'store'])->name('jadwal.store');
        Route::put('jadwal/{jadwal}', [JadwalController::class, 'update'])->name('jadwal.update');
        Route::delete('jadwal/{jadwal}', [JadwalController::class, 'destroy'])->name('jadwal.destroy');
    });

    // --- Izin / cuti ---
    Route::get('izin', [LeaveRequestController::class, 'index'])->name('leaves.index')->middleware('permission:leave_apply|leave_approve');
    Route::post('izin', [LeaveRequestController::class, 'store'])->name('leaves.store')->middleware('permission:leave_apply');
    Route::post('izin/{leave}/approve', [LeaveRequestController::class, 'approve'])->name('leaves.approve')->middleware('permission:leave_approve');
    Route::post('izin/{leave}/reject', [LeaveRequestController::class, 'reject'])->name('leaves.reject')->middleware('permission:leave_approve');

    // --- Tukar jam ---
    Route::get('tukar-jam', [SwapRequestController::class, 'index'])->name('swaps.index')->middleware('permission:swap_apply|swap_approve');
    Route::post('tukar-jam', [SwapRequestController::class, 'store'])->name('swaps.store')->middleware('permission:swap_apply');
    Route::post('tukar-jam/{swap}/approve', [SwapRequestController::class, 'approve'])->name('swaps.approve')->middleware('permission:swap_approve');
    Route::post('tukar-jam/{swap}/reject', [SwapRequestController::class, 'reject'])->name('swaps.reject')->middleware('permission:swap_approve');

    // --- Penggajian ---
    Route::get('penggajian/slip', [PayrollController::class, 'slip'])->name('payroll.slip')->middleware('permission:payroll_view');
    Route::middleware('permission:payroll_manage')->group(function () {
        Route::get('penggajian', [PayrollController::class, 'index'])->name('payroll.index');
        Route::post('penggajian', [PayrollController::class, 'store'])->name('payroll.store');
        Route::get('penggajian/{period}', [PayrollController::class, 'show'])->name('payroll.show');
        Route::get('penggajian/{period}/export', [PayrollController::class, 'export'])->name('payroll.export');
        Route::post('penggajian/{period}/proses', [PayrollController::class, 'process'])->name('payroll.process');
        Route::post('penggajian/{period}/finalisasi', [PayrollController::class, 'finalize'])->name('payroll.finalize');
    });

    // --- Data santri & kartu ---
    Route::get('santri', [SantriController::class, 'index'])->name('santri.index')->middleware('permission:santri_view');
    Route::get('santri/{santri}/kartu', [SantriController::class, 'card'])->name('santri.card')->middleware('permission:santri_view');
    Route::get('santri/{santri}/riwayat', [SantriController::class, 'history'])->name('santri.history')->middleware('permission:santri_view');
    Route::middleware('permission:santri_view')->group(function () {
        Route::post('santri/cards', [SantriController::class, 'bulkCards'])->name('santri.bulk-cards');
        Route::post('santri/export', [SantriController::class, 'export'])->name('santri.export');
    });
    Route::middleware('permission:santri_manage')->group(function () {
        Route::post('santri', [SantriController::class, 'store'])->name('santri.store');
        Route::put('santri/{santri}', [SantriController::class, 'update'])->name('santri.update');
        Route::delete('santri/{santri}', [SantriController::class, 'destroy'])->name('santri.destroy');
        Route::post('santri/{santri}/regenerate-card', [SantriController::class, 'regenerateCard'])->name('santri.regenerate-card');
        Route::post('santri/bulk-regenerate', [SantriController::class, 'bulkRegenerate'])->name('santri.bulk-regenerate');
        Route::post('santri/bulk-move-class', [SantriController::class, 'bulkMoveClass'])->name('santri.bulk-move-class');
    });

    // --- Presensi santri (scan) ---
    Route::get('presensi-santri', [SantriPresenceController::class, 'index'])->name('santri-presensi.index')->middleware('permission:santri_presence');
    Route::post('presensi-santri/scan', [SantriPresenceController::class, 'scan'])->name('santri-presensi.scan')->middleware('permission:santri_presence');

    // --- Kelas ---
    Route::get('kelas', [KelasController::class, 'index'])->name('kelas.index')->middleware('permission:class_view');
    Route::get('kelas/naik-kelas', [KelasController::class, 'promote'])->name('kelas.promote')->middleware('permission:class_manage');
    Route::post('kelas/naik-kelas', [KelasController::class, 'processPromote'])->name('kelas.promote.process')->middleware('permission:class_manage');
    Route::get('kelas/{kela}/anggota', [KelasController::class, 'members'])->name('kelas.members')->middleware('permission:class_view');
    Route::middleware('permission:class_manage')->group(function () {
        Route::post('kelas', [KelasController::class, 'store'])->name('kelas.store');
        Route::put('kelas/{kela}', [KelasController::class, 'update'])->name('kelas.update');
        Route::delete('kelas/{kela}', [KelasController::class, 'destroy'])->name('kelas.destroy');
        Route::post('kelas/{kela}/anggota', [KelasController::class, 'addMembers'])->name('kelas.members.add');
    });

    // --- Master akademik (tahun ajaran, mapel, sesi) ---
    Route::middleware('permission:academic_manage')->group(function () {
        Route::resource('tahun-ajaran', TahunAjaranController::class)->except(['show', 'create', 'edit'])->parameters(['tahun-ajaran' => 'tahunAjaran']);
        Route::resource('mapel', MapelController::class)->except(['show', 'create', 'edit']);
        Route::resource('sesi', SesiController::class)->except(['show', 'create', 'edit']);
    });

    // --- Perilaku (pelanggaran & kebaikan) ---
    Route::get('perilaku', [BehaviorController::class, 'index'])->name('behaviors.index')->middleware('permission:behavior_log');
    Route::post('perilaku', [BehaviorController::class, 'store'])->name('behaviors.store')->middleware('permission:behavior_log');
    Route::delete('perilaku/{behavior}', [BehaviorController::class, 'destroy'])->name('behaviors.destroy')->middleware('permission:behavior_log');

    // --- Nilai / perkembangan ---
    Route::get('nilai', [GradeController::class, 'index'])->name('grades.index')->middleware('permission:grade_log');
    Route::post('nilai', [GradeController::class, 'store'])->name('grades.store')->middleware('permission:grade_log');
    Route::delete('nilai/{grade}', [GradeController::class, 'destroy'])->name('grades.destroy')->middleware('permission:grade_log');

    // --- Kunjungan / jenguk ---
    Route::get('kunjungan', [VisitController::class, 'index'])->name('visits.index')->middleware('permission:visit_log');
    Route::post('kunjungan', [VisitController::class, 'store'])->name('visits.store')->middleware('permission:visit_log');
    Route::delete('kunjungan/{visit}', [VisitController::class, 'destroy'])->name('visits.destroy')->middleware('permission:visit_log');

    // --- Data personil ---
    Route::get('personil', [PersonilController::class, 'index'])->name('personil.index')->middleware('permission:personnel_view');
    Route::get('personil/export', [PersonilController::class, 'export'])->name('personil.export')->middleware('permission:personnel_view');
    Route::get('personil/{personil}', [PersonilController::class, 'show'])->name('personil.show')->middleware('permission:personnel_view');
    Route::middleware('permission:personnel_manage')->group(function () {
        Route::post('personil', [PersonilController::class, 'store'])->name('personil.store');
        Route::put('personil/{personil}', [PersonilController::class, 'update'])->name('personil.update');
        Route::delete('personil/{personil}', [PersonilController::class, 'destroy'])->name('personil.destroy');
        Route::post('personil/{personil}/dokumen', [PersonilController::class, 'storeDocument'])->name('personil.documents.store');
        Route::delete('personil/dokumen/{document}', [PersonilController::class, 'destroyDocument'])->name('personil.documents.destroy');
    });
    Route::get('personil/dokumen/{document}/unduh', [PersonilController::class, 'downloadDocument'])->name('personil.documents.download')->middleware('permission:personnel_view');

    // --- Manajemen user ---
    Route::resource('users', UserController::class)->except(['show', 'create', 'edit'])->middleware('permission:user_manage');
    Route::post('users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password')->middleware('permission:user_manage');

    // --- Role & permission ---
    Route::get('roles', [RoleController::class, 'index'])->name('roles.index')->middleware('permission:role_manage');
    Route::post('roles', [RoleController::class, 'store'])->name('roles.store')->middleware('permission:role_manage');
    Route::put('roles/{role}', [RoleController::class, 'update'])->name('roles.update')->middleware('permission:role_manage');
    Route::put('roles/{role}/permissions', [RoleController::class, 'updatePermissions'])->name('roles.permissions')->middleware('permission:role_manage');
    Route::delete('roles/{role}', [RoleController::class, 'destroy'])->name('roles.destroy')->middleware('permission:role_manage');

    // --- Laporan strategis ---
    Route::get('laporan', [ReportController::class, 'index'])->name('reports.index')->middleware('permission:reports_view');
    Route::get('laporan/export', [ReportController::class, 'export'])->name('reports.export')->middleware('permission:reports_view');

    // --- Integrasi WhatsApp (Fonnte) ---
    Route::middleware('permission:setting_manage')->group(function () {
        Route::get('whatsapp', [WhatsappController::class, 'index'])->name('whatsapp.index');
        Route::put('whatsapp', [WhatsappController::class, 'update'])->name('whatsapp.update');
        Route::post('whatsapp/test', [WhatsappController::class, 'test'])->name('whatsapp.test');

        // --- Branding & landing page ---
        Route::get('branding', [BrandingController::class, 'edit'])->name('branding.edit');
        Route::put('branding', [BrandingController::class, 'update'])->name('branding.update');
    });
});
