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

        $mySchedules = ($personil && $personil->isPengajar())
            ? $personil->jadwals()->with(['kelas', 'mapel', 'sesi'])->get()
            : collect();

        $myRequests = $personil
            ? SwapRequest::with(['jadwal.kelas', 'jadwal.mapel', 'target'])
                ->where('requester_personil_id', $personil->id)->latest('id')->take(20)->get()
            : collect();

        $pending = $canApprove
            ? SwapRequest::with(['jadwal.kelas', 'jadwal.mapel', 'requester', 'target'])
                ->where('status', 'Diajukan')->latest('id')->get()
            : collect();

        // Daftar pengajar lain sebagai kandidat guru pengganti.
        $teachers = Personil::where('is_active', true)
            ->whereIn('fungsi_kerja', ['Pengajar', 'Dua Fungsi'])
            ->when($personil, fn ($q) => $q->where('id', '!=', $personil->id))
            ->orderBy('name')->get();

        return view('tukar-jam.index', compact('mySchedules', 'myRequests', 'pending', 'canApprove', 'teachers'));
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
            'target_personil_id' => ['nullable', 'exists:personils,id'],
            'reason' => ['required', 'string', 'max:1000'],
        ]);

        // Pastikan jadwal yang ditukar milik pengaju.
        $jadwal = Jadwal::where('id', $data['jadwal_id'])->where('personil_id', $personil->id)->first();
        if (! $jadwal) {
            return back()->with('error', 'Jadwal yang dipilih bukan milik Anda.');
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
