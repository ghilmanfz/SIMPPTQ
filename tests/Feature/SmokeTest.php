<?php

namespace Tests\Feature;

use App\Models\LeaveRequest;
use App\Models\PayrollPeriod;
use App\Models\Personil;
use App\Models\PresensiPersonil;
use App\Models\Santri;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class SmokeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    private function user(string $email): User
    {
        return User::where('email', $email)->firstOrFail();
    }

    public function test_halaman_publik_dapat_diakses(): void
    {
        $this->get('/')->assertOk();
        $this->get('/login')->assertOk();
    }

    public function test_login_berhasil_dan_gagal(): void
    {
        $this->post('/login', ['email' => 'petugas@nuruliman.net', 'password' => 'petugas123'])
            ->assertRedirect(route('app.dashboard'));
        $this->assertAuthenticated();

        $this->post('/logout');

        $this->post('/login', ['email' => 'petugas@nuruliman.net', 'password' => 'salah'])
            ->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_halaman_login_tampil_untuk_tamu(): void
    {
        $this->get('/login')->assertOk();
    }

    public function test_user_login_diarahkan_dari_halaman_login_ke_dashboard(): void
    {
        $admin = $this->user('petugas@nuruliman.net');
        $this->actingAs($admin)->get('/login')->assertRedirect('/app');
    }

    public function test_superadmin_membuka_semua_halaman(): void
    {
        $admin = $this->user('superadmin@nuruliman.net');
        $santri = Santri::first();
        $personil = Personil::first();
        $period = PayrollPeriod::first();

        $paths = [
            '/app', '/app/announcements', '/app/presensi', '/app/presensi/rekap', '/app/jadwal',
            '/app/izin', '/app/tukar-jam', '/app/penggajian', '/app/penggajian/slip',
            "/app/penggajian/{$period->id}", '/app/santri', "/app/santri/{$santri->id}/kartu",
            '/app/presensi-santri', '/app/kelas', '/app/mapel', '/app/perilaku', '/app/nilai',
            '/app/kunjungan', '/app/personil', "/app/personil/{$personil->id}", '/app/users',
            '/app/roles', '/app/laporan', '/app/whatsapp', '/app/branding', '/app/profile', '/app/lokasi',
        ];

        foreach ($paths as $path) {
            $this->actingAs($admin)->get($path)->assertOk();
        }
    }

    public function test_setiap_role_membuka_dashboard(): void
    {
        foreach (['admin', 'teacher', 'staff', 'hybrid', 'leader'] as $role) {
            $email = match ($role) {
                'admin' => 'petugas@nuruliman.net',
                'teacher' => 'ustadz.ahmad@nuruliman.net',
                'staff' => 'staff.budiyono@nuruliman.net',
                'hybrid' => 'ustadz.fatkur@nuruliman.net',
                'leader' => 'pimpinan.kiai@nuruliman.net',
            };
            $this->actingAs($this->user($email))->get('/app')->assertOk();
        }
    }

    public function test_otorisasi_membatasi_akses(): void
    {
        $staff = $this->user('staff.budiyono@nuruliman.net');

        // Staf non-pengajar tidak boleh membuka modul admin/santri.
        $this->actingAs($staff)->get('/app/roles')->assertForbidden();
        $this->actingAs($staff)->get('/app/santri')->assertForbidden();
        $this->actingAs($staff)->get('/app/users')->assertForbidden();

        // Tetapi boleh presensi & izin.
        $this->actingAs($staff)->get('/app/presensi')->assertOk();
        $this->actingAs($staff)->get('/app/izin')->assertOk();
    }

    public function test_admin_dapat_menambah_santri(): void
    {
        $admin = $this->user('petugas@nuruliman.net');

        $this->actingAs($admin)->post('/app/santri', [
            'name' => 'Santri Uji Coba',
            'nis' => '99999',
            'status' => 'Aktif',
        ])->assertRedirect();

        $this->assertDatabaseHas('santris', ['nis' => '99999', 'name' => 'Santri Uji Coba']);
    }

    public function test_presensi_gps_valid_dan_diluar_radius(): void
    {
        $fatkur = $this->user('ustadz.fatkur@nuruliman.net'); // belum presensi hari ini

        // Koordinat di dalam radius lokasi pondok.
        $this->actingAs($fatkur)->post('/app/presensi/check-in', [
            'latitude' => -6.9147440, 'longitude' => 107.6098100,
        ])->assertRedirect();

        $this->assertTrue(
            PresensiPersonil::where('personil_id', $fatkur->personil->id)->whereDate('date', Carbon::today())->whereNotNull('check_in_time')->exists()
        );

        // Koordinat jauh (Jakarta) harus ditolak. Admin punya izin presensi & belum presensi hari ini.
        $admin = $this->user('petugas@nuruliman.net');
        $this->actingAs($admin)->post('/app/presensi/check-in', [
            'latitude' => -6.2000000, 'longitude' => 106.8166600,
        ])->assertSessionHas('error');

        $this->assertFalse(
            PresensiPersonil::where('personil_id', $admin->personil->id)->whereDate('date', Carbon::today())->exists()
        );
    }

    public function test_admin_menyetujui_izin(): void
    {
        $admin = $this->user('petugas@nuruliman.net');
        $pending = LeaveRequest::where('status', 'Diajukan')->firstOrFail();

        $this->actingAs($admin)->post("/app/izin/{$pending->id}/approve")->assertRedirect();

        $this->assertDatabaseHas('leave_requests', ['id' => $pending->id, 'status' => 'Disetujui']);
    }
}
