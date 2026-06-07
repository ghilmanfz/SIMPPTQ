<?php

namespace App\Http\Controllers;

use App\Models\TahunAjaran;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TahunAjaranController extends Controller
{
    public function index(): RedirectResponse
    {
        return redirect()->route('app.mapel.index');
    }

    public function store(Request $request): RedirectResponse
    {
        $this->persist($this->validateData($request), null);

        return back()->with('success', 'Tahun ajaran berhasil ditambahkan.');
    }

    public function update(Request $request, TahunAjaran $tahunAjaran): RedirectResponse
    {
        $this->persist($this->validateData($request), $tahunAjaran);

        return back()->with('success', 'Tahun ajaran berhasil diperbarui.');
    }

    public function destroy(TahunAjaran $tahunAjaran): RedirectResponse
    {
        $tahunAjaran->delete();

        return back()->with('success', 'Tahun ajaran berhasil dihapus.');
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function persist(array $data, ?TahunAjaran $model): void
    {
        $data['is_active'] = $data['is_active'] ?? false;

        // Hanya satu tahun ajaran yang boleh aktif.
        if ($data['is_active']) {
            TahunAjaran::where('is_active', true)->update(['is_active' => false]);
        }

        $model ? $model->update($data) : TahunAjaran::create($data);
    }

    /**
     * @return array<string, mixed>
     */
    private function validateData(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'is_active' => ['nullable', 'boolean'],
        ]) + ['is_active' => $request->boolean('is_active')];
    }
}
