<?php

namespace App\Http\Controllers;

use App\Models\Santri;
use App\Models\Visit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VisitController extends Controller
{
    public function index(): View
    {
        $visits = Visit::with('santri', 'recorder')->latest('visit_at')->paginate(12);
        $santriList = Santri::where('status', 'Aktif')->orderBy('name')->get();

        return view('kunjungan.index', compact('visits', 'santriList'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'santri_id' => ['required', 'exists:santris,id'],
            'visitor_name' => ['required', 'string', 'max:150'],
            'relation' => ['nullable', 'string', 'max:50'],
            'visit_at' => ['required', 'date'],
            'purpose' => ['nullable', 'string', 'max:150'],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);
        $data['recorded_by'] = auth()->id();

        Visit::create($data);

        return back()->with('success', 'Kunjungan wali santri berhasil dicatat.');
    }

    public function destroy(Visit $visit): RedirectResponse
    {
        $visit->delete();

        return back()->with('success', 'Catatan kunjungan berhasil dihapus.');
    }
}
