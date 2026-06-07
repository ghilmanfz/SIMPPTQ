<?php

namespace App\Http\Controllers;

use App\Models\ClassHistory;
use App\Models\Kelas;
use App\Models\Personil;
use App\Models\Santri;
use App\Models\TahunAjaran;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class KelasController extends Controller
{
    public function index(): View
    {
        $kelas = Kelas::with('waliKelas', 'tahunAjaran')->withCount('santris')->orderBy('name')->get();
        $waliOptions = Personil::where('is_active', true)
            ->whereIn('fungsi_kerja', ['Pengajar', 'Dua Fungsi'])
            ->orderBy('name')->get();
        $tahunAjarans = TahunAjaran::orderByDesc('is_active')->orderByDesc('id')->get();
        $canManage = auth()->user()->hasPermissionTo('class_manage');

        return view('kelas.index', compact('kelas', 'waliOptions', 'tahunAjarans', 'canManage'));
    }

    public function store(Request $request): RedirectResponse
    {
        Kelas::create($this->validateData($request));

        return back()->with('success', 'Kelas berhasil ditambahkan.');
    }

    /**
     * Halaman kelola anggota satu kelas (lihat, tambah, keluarkan, pindahkan).
     */
    public function members(Kelas $kela): View
    {
        $kela->load('tahunAjaran', 'waliKelas');

        $members = Santri::with('kelas')->where('kelas_id', $kela->id)->orderBy('name')->get();

        // Calon anggota: santri aktif yang belum berada di kelas ini.
        $available = Santri::with('kelas')
            ->whereNotIn('status', ['Lulus', 'Keluar'])
            ->where(fn ($q) => $q->whereNull('kelas_id')->orWhere('kelas_id', '!=', $kela->id))
            ->orderBy('name')->get();

        $kelasList = Kelas::where('id', '!=', $kela->id)->orderBy('name')->get();
        $canManage = auth()->user()->hasPermissionTo('class_manage');

        return view('kelas.members', compact('kela', 'members', 'available', 'kelasList', 'canManage'));
    }

    /**
     * Tambahkan santri terpilih ke kelas ini.
     */
    public function addMembers(Kelas $kela, Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'santri_ids' => ['required', 'array', 'min:1'],
            'santri_ids.*' => ['integer', 'exists:santris,id'],
        ]);

        $count = 0;
        foreach (Santri::with('kelas')->whereIn('id', $validated['santri_ids'])->get() as $santri) {
            $from = $santri->kelas;
            if ($from?->id === $kela->id) {
                continue;
            }
            $santri->update(['kelas_id' => $kela->id]);
            ClassHistory::record($santri, $from ? 'Pindah Kelas' : 'Penempatan', $from, $kela, 'Ditambahkan via Kelola Anggota.');
            $count++;
        }

        return back()->with('success', "{$count} santri berhasil ditambahkan ke kelas {$kela->name}.");
    }

    /**
     * Wizard naik kelas / promosi tahunan.
     */
    public function promote(): View
    {
        $sourceKelas = Kelas::with('tahunAjaran')->withCount('santris')
            ->having('santris_count', '>', 0)->orderBy('name')->get();
        $targetKelas = Kelas::with('tahunAjaran')->orderBy('name')->get();
        $tahunAjarans = TahunAjaran::orderByDesc('is_active')->orderByDesc('id')->get();

        // Daftar santri per kelas (untuk centang per anak).
        $santriByKelas = Santri::whereNotNull('kelas_id')
            ->whereNotIn('status', ['Lulus', 'Keluar'])
            ->orderBy('name')->get()->groupBy('kelas_id');

        return view('kelas.promote', compact('sourceKelas', 'targetKelas', 'tahunAjarans', 'santriByKelas'));
    }

    /**
     * Proses wizard naik kelas: pindah / luluskan / biarkan per kelas & per santri.
     */
    public function processPromote(Request $request): RedirectResponse
    {
        $request->validate([
            'actions' => ['required', 'array'],
        ]);

        $actions = $request->input('actions', []);   // [kelasId => 'move'|'graduate'|'skip']
        $targets = $request->input('targets', []);    // [kelasId => targetKelasId]
        $excluded = array_map('intval', $request->input('exclude', [])); // santri_id yang tidak diproses

        $moved = 0;
        $graduated = 0;
        $stayed = 0;

        DB::transaction(function () use ($actions, $targets, $excluded, &$moved, &$graduated, &$stayed): void {
            foreach ($actions as $kelasId => $action) {
                if (! in_array($action, ['move', 'graduate'], true)) {
                    continue;
                }

                $from = Kelas::find($kelasId);
                if (! $from) {
                    continue;
                }

                $target = $action === 'move' ? Kelas::find($targets[$kelasId] ?? null) : null;
                if ($action === 'move' && ! $target) {
                    continue; // pindah tapi tujuan kosong -> lewati
                }

                $santris = Santri::where('kelas_id', $from->id)
                    ->whereNotIn('status', ['Lulus', 'Keluar'])->get();

                foreach ($santris as $santri) {
                    if (in_array($santri->id, $excluded, true)) {
                        ClassHistory::record($santri, 'Tinggal Kelas', $from, $from, 'Tidak naik pada proses naik kelas.');
                        $stayed++;
                        continue;
                    }

                    if ($action === 'graduate') {
                        $santri->update(['status' => 'Lulus', 'kelas_id' => null]);
                        ClassHistory::record($santri, 'Lulus', $from, null, 'Diluluskan via wizard naik kelas.');
                        $graduated++;
                    } else {
                        $santri->update(['kelas_id' => $target->id]);
                        ClassHistory::record($santri, 'Naik Kelas', $from, $target, 'Naik kelas via wizard.');
                        $moved++;
                    }
                }
            }
        });

        return redirect()->route('app.kelas.index')
            ->with('success', "Proses naik kelas selesai: {$moved} naik, {$graduated} lulus, {$stayed} tinggal kelas.");
    }

    public function update(Request $request, Kelas $kela): RedirectResponse
    {
        $kela->update($this->validateData($request, $kela));

        return back()->with('success', 'Kelas berhasil diperbarui.');
    }

    public function destroy(Kelas $kela): RedirectResponse
    {
        if ($kela->santris()->exists()) {
            return back()->with('error', 'Kelas masih memiliki santri. Pindahkan santri terlebih dahulu.');
        }

        $kela->delete();

        return back()->with('success', 'Kelas berhasil dihapus.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validateData(Request $request, ?Kelas $kelas = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:50', Rule::unique('kelas')->ignore($kelas?->id)],
            'tingkat' => ['nullable', 'string', 'max:20'],
            'tahun_ajaran_id' => ['nullable', 'exists:tahun_ajarans,id'],
            'wali_kelas_id' => ['nullable', 'exists:personils,id'],
            'description' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ]) + ['is_active' => $request->boolean('is_active')];
    }
}
