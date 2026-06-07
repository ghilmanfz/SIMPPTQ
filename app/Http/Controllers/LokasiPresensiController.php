<?php

namespace App\Http\Controllers;

use App\Models\LokasiPresensi;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LokasiPresensiController extends Controller
{
    public function index(): View
    {
        $lokasi = LokasiPresensi::orderBy('name')->get();

        return view('lokasi.index', compact('lokasi'));
    }

    public function store(Request $request): RedirectResponse
    {
        LokasiPresensi::create($this->validateData($request));

        return back()->with('success', 'Lokasi presensi berhasil ditambahkan.');
    }

    public function update(Request $request, LokasiPresensi $lokasi): RedirectResponse
    {
        $lokasi->update($this->validateData($request));

        return back()->with('success', 'Lokasi presensi berhasil diperbarui.');
    }

    public function destroy(LokasiPresensi $lokasi): RedirectResponse
    {
        $lokasi->delete();

        return back()->with('success', 'Lokasi presensi berhasil dihapus.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validateData(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'radius' => ['required', 'integer', 'min:10', 'max:5000'],
            'is_active' => ['nullable', 'boolean'],
        ]) + ['is_active' => $request->boolean('is_active')];
    }
}
