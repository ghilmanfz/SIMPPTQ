<?php

namespace App\Http\Controllers;

use App\Models\Mapel;
use App\Models\Sesi;
use App\Models\TahunAjaran;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MapelController extends Controller
{
    /**
     * Halaman gabungan Master Akademik: mapel/halaqah, sesi, dan tahun ajaran.
     */
    public function index(): View
    {
        $mapels = Mapel::orderBy('name')->get();
        $sesis = Sesi::orderBy('order')->get();
        $tahunAjarans = TahunAjaran::orderByDesc('is_active')->orderByDesc('id')->get();

        return view('akademik.index', compact('mapels', 'sesis', 'tahunAjarans'));
    }

    public function store(Request $request): RedirectResponse
    {
        Mapel::create($this->validateData($request));

        return back()->with('success', 'Mapel/Halaqah berhasil ditambahkan.');
    }

    public function update(Request $request, Mapel $mapel): RedirectResponse
    {
        $mapel->update($this->validateData($request));

        return back()->with('success', 'Mapel/Halaqah berhasil diperbarui.');
    }

    public function destroy(Mapel $mapel): RedirectResponse
    {
        $mapel->delete();

        return back()->with('success', 'Mapel/Halaqah berhasil dihapus.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validateData(Request $request): array
    {
        return $request->validate([
            'code' => ['nullable', 'string', 'max:30'],
            'name' => ['required', 'string', 'max:150'],
            'type' => ['required', 'in:Mapel,Halaqah'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);
    }
}
