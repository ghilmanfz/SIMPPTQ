<?php

namespace App\Http\Controllers;

use App\Models\Jadwal;
use App\Models\Personil;
use App\Models\SwapRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class SwapRequestController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();
        $personil = $user->personil;
        $canApprove = $user->hasPermissionTo('swap_approve');

        // Semua jadwal (untuk memilih jadwal sendiri ATAU jadwal rekan yang ingin digantikan).
        $allSchedules = ($personil && $personil->isPengajar())
            ? Jadwal::with(['personil', 'kelas', 'mapel', 'sesi'])->orderByRaw("FIELD(day,'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Ahad')")->orderBy('start_time')->get()
            : collect();

        $myRequests = $personil
            ? SwapRequest::with(['jadwal.kelas', 'jadwal.mapel', 'target'])
                ->where('requester_personil_id', $personil->id)->latest('id')->take(20)->get()
            : collect();

        $pending = $canApprove
            ? SwapRequest::with(['jadwal.kelas', 'jadwal.mapel', 'jadwal.personil', 'requester', 'target'])
                ->where('status', 'Diajukan')->latest('id')->get()
            : collect();

        // Semua pengajar aktif sebagai kandidat guru pengganti (termasuk diri sendiri,
        // agar bisa mengajukan diri menggantikan jadwal rekan).
        $teachers = Personil::where('is_active', true)
            ->whereIn('fungsi_kerja', ['Pengajar', 'Dua Fungsi'])
            ->orderBy('name')->get();

        return view('tukar-jam.index', compact('allSchedules', 'myRequests', 'pending', 'canApprove', 'teachers', 'personil'));
    }

    public function store(Request $request): RedirectResponse
    {
        $personil = auth()->user()->personil;
        if (! $personil || ! $personil->isPengajar()) {
            return back()->with('error', 'Hanya personil pengajar yang dapat mengajukan tukar jam.');
        }

        $data = $request->validate([
            'jadwal_id' => ['required', 'exists:jadwals,id'],
            'date' => ['required', 'date'],
            'target_personil_id' => ['required', 'exists:personils,id'],
            'reason' => ['required', 'string', 'max:1000'],
        ]);

        $jadwal = Jadwal::find($data['jadwal_id']);
        $ownerId = $jadwal->personil_id;
        $substituteId = (int) $data['target_personil_id'];

        // Pengaju harus pemilik jadwal (minta digantikan) ATAU calon pengganti (menawarkan diri).
        if ($personil->id !== $ownerId && $personil->id !== $substituteId) {
            return back()->with('error', 'Anda hanya boleh mengajukan untuk jadwal sendiri, atau saat Anda menjadi penggantinya.');
        }
        if ($ownerId === $substituteId) {
            return back()->with('error', 'Guru pengganti tidak boleh sama dengan pengajar asli jadwal tersebut.');
        }

        SwapRequest::create($data + [
            'requester_personil_id' => $personil->id,
            'status' => 'Diajukan',
        ]);

        return back()->with('success', 'Pengajuan tukar jam terkirim, menunggu persetujuan.');
    }

    public function approve(SwapRequest $swap): RedirectResponse
    {
        $swap->update([
            'status' => 'Diterapkan',
            'approved_by' => auth()->id(),
            'approved_at' => Carbon::now(),
        ]);

        // Catat sebagai pengecualian jadwal pada tanggal tsb — jadwal master tetap utuh.
        $swap->jadwal->exceptions()->create([
            'date' => $swap->date,
            'type' => 'Tukar',
            'substitute_personil_id' => $swap->target_personil_id,
            'swap_request_id' => $swap->id,
            'note' => 'Pengganti hasil persetujuan tukar jam.',
        ]);

        return back()->with('success', 'Tukar jam disetujui & jadwal pengganti dibuat untuk tanggal tersebut.');
    }

    public function reject(SwapRequest $swap): RedirectResponse
    {
        $swap->update([
            'status' => 'Ditolak',
            'approved_by' => auth()->id(),
            'approved_at' => Carbon::now(),
        ]);

        return back()->with('success', 'Pengajuan tukar jam ditolak. Jadwal tetap mengikuti master.');
    }
}
