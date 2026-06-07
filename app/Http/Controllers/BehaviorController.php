<?php

namespace App\Http\Controllers;

use App\Models\Behavior;
use App\Models\Santri;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BehaviorController extends Controller
{
    public function index(): View
    {
        $behaviors = Behavior::with('santri', 'recorder')->latest('date')->latest('id')->paginate(12);
        $santriList = Santri::where('status', 'Aktif')->orderBy('name')->get();

        return view('perilaku.index', compact('behaviors', 'santriList'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'santri_id' => ['required', 'exists:santris,id'],
            'date' => ['required', 'date'],
            'type' => ['required', 'in:Pelanggaran,Kebaikan'],
            'category' => ['nullable', 'string', 'max:100'],
            'points' => ['required', 'integer', 'min:0', 'max:1000'],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);
        $data['recorded_by'] = auth()->id();

        Behavior::create($data);

        return back()->with('success', 'Catatan perilaku santri berhasil disimpan.');
    }

    public function destroy(Behavior $behavior): RedirectResponse
    {
        $behavior->delete();

        return back()->with('success', 'Catatan perilaku berhasil dihapus.');
    }
}
