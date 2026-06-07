<?php

namespace App\Http\Controllers;

use App\Models\Grade;
use App\Models\Mapel;
use App\Models\Santri;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GradeController extends Controller
{
    public function index(): View
    {
        $grades = Grade::with('santri', 'mapel', 'recorder')->latest('date')->latest('id')->paginate(12);
        $santriList = Santri::where('status', 'Aktif')->orderBy('name')->get();
        $mapels = Mapel::orderBy('name')->get();

        return view('nilai.index', compact('grades', 'santriList', 'mapels'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'santri_id' => ['required', 'exists:santris,id'],
            'mapel_id' => ['nullable', 'exists:mapels,id'],
            'subject' => ['nullable', 'string', 'max:150'],
            'score' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'date' => ['required', 'date'],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);
        $data['recorded_by'] = auth()->id();

        Grade::create($data);

        return back()->with('success', 'Nilai/perkembangan santri berhasil disimpan.');
    }

    public function destroy(Grade $grade): RedirectResponse
    {
        $grade->delete();

        return back()->with('success', 'Catatan nilai berhasil dihapus.');
    }
}
