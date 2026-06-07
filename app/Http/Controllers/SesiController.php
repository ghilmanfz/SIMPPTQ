<?php

namespace App\Http\Controllers;

use App\Models\Sesi;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SesiController extends Controller
{
    public function index(): RedirectResponse
    {
        return redirect()->route('app.mapel.index');
    }

    public function store(Request $request): RedirectResponse
    {
        Sesi::create($this->validateData($request));

        return back()->with('success', 'Sesi/jam pelajaran berhasil ditambahkan.');
    }

    public function update(Request $request, Sesi $sesi): RedirectResponse
    {
        $sesi->update($this->validateData($request));

        return back()->with('success', 'Sesi berhasil diperbarui.');
    }

    public function destroy(Sesi $sesi): RedirectResponse
    {
        $sesi->delete();

        return back()->with('success', 'Sesi berhasil dihapus.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validateData(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'order' => ['nullable', 'integer', 'min:0'],
        ]);
    }
}
