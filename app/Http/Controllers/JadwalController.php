<?php

namespace App\Http\Controllers;

use App\Models\Jadwal;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\Personil;
use App\Models\Sesi;
use App\Models\TahunAjaran;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class JadwalController extends Controller
{
    private const DAYS = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Ahad'];

    public function index(): View
    {
        $user = auth()->user();
        $canManage = $user->hasPermissionTo('schedule_manage');

        $query = Jadwal::with(['personil', 'kelas', 'mapel', 'sesi']);

        // Pengajar tanpa hak kelola hanya melihat jadwalnya sendiri.
        if (! $canManage && $user->personil) {
            $query->where('personil_id', $user->personil->id);
        }

        // Urutan hari ditangani view (iterasi array $days); cukup urutkan per jam.
        $jadwals = $query->orderBy('start_time')->get()->groupBy('day');

        $pengajar = Personil::where('is_active', true)->whereIn('fungsi_kerja', ['Pengajar', 'Dua Fungsi'])->orderBy('name')->get();
        $kelasList = Kelas::orderBy('name')->get();
        $mapels = Mapel::orderBy('name')->get();
        $sesis = Sesi::orderBy('order')->get();
        $days = self::DAYS;

        return view('jadwal.index', compact('jadwals', 'canManage', 'pengajar', 'kelasList', 'mapels', 'sesis', 'days'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateData($request);
        $this->ensureNoConflict($data);

        $this->applySesiTime($data);
        Jadwal::create($data);

        return back()->with('success', 'Jadwal mengajar berhasil ditambahkan.');
    }

    public function update(Request $request, Jadwal $jadwal): RedirectResponse
    {
        $data = $this->validateData($request);
        $this->ensureNoConflict($data, $jadwal->id);

        $this->applySesiTime($data);
        $jadwal->update($data);

        return back()->with('success', 'Jadwal mengajar berhasil diperbarui.');
    }

    public function destroy(Jadwal $jadwal): RedirectResponse
    {
        $jadwal->delete();

        return back()->with('success', 'Jadwal mengajar berhasil dihapus.');
    }

    /**
     * Cegah bentrok: pengajar atau kelas yang sama pada hari & sesi yang sama.
     *
     * @param  array<string, mixed>  $data
     */
    private function ensureNoConflict(array $data, ?int $ignoreId = null): void
    {
        $conflict = Jadwal::where('day', $data['day'])
            ->where('sesi_id', $data['sesi_id'])
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->where(fn ($q) => $q->where('personil_id', $data['personil_id'])->orWhere('kelas_id', $data['kelas_id']))
            ->exists();

        if ($conflict) {
            throw ValidationException::withMessages([
                'sesi_id' => 'Bentrok jadwal: pengajar atau kelas sudah terisi pada hari & sesi tersebut.',
            ]);
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function applySesiTime(array &$data): void
    {
        if ($sesi = Sesi::find($data['sesi_id'])) {
            $data['start_time'] = $sesi->start_time;
            $data['end_time'] = $sesi->end_time;
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function validateData(Request $request): array
    {
        $data = $request->validate([
            'personil_id' => ['required', 'exists:personils,id'],
            'kelas_id' => ['required', 'exists:kelas,id'],
            'mapel_id' => ['nullable', 'exists:mapels,id'],
            'sesi_id' => ['required', 'exists:sesis,id'],
            'day' => ['required', 'in:' . implode(',', self::DAYS)],
        ]);

        $data['tahun_ajaran_id'] = TahunAjaran::where('is_active', true)->value('id');

        return $data;
    }
}
