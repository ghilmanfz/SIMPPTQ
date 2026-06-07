<?php

namespace App\Http\Controllers;

use App\Models\ClassHistory;
use App\Models\Kelas;
use App\Models\Santri;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Support\Branding;
use App\Support\ExcelExporter;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SantriController extends Controller
{
    public function index(Request $request): View
    {
        $query = Santri::query()->with('kelas');

        if ($request->filled('kelas_id')) {
            $query->where('kelas_id', $request->kelas_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(fn ($sub) => $sub->where('name', 'like', "%{$q}%")->orWhere('nis', 'like', "%{$q}%"));
        }

        $santri = $query->orderBy('name')->paginate(10)->withQueryString();
        $kelasList = Kelas::orderBy('name')->get();
        $canManage = auth()->user()->hasPermissionTo('santri_manage');

        return view('santri.index', compact('santri', 'kelasList', 'canManage'));
    }

    public function card(Santri $santri): View
    {
        return view('santri.card', compact('santri'));
    }

    /**
     * Timeline riwayat kelas seorang santri (penempatan, pindah, naik kelas, lulus).
     */
    public function history(Santri $santri): View
    {
        $santri->load('kelas');
        $histories = $santri->classHistories()->with(['kelas', 'tahunAjaran', 'creator'])->get();

        return view('santri.history', compact('santri', 'histories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateData($request);
        $data['photo_path'] = $this->handlePhoto($request);
        $data['card_token'] = Santri::generateCardToken();

        $santri = Santri::create($data);

        if ($santri->kelas_id) {
            ClassHistory::record($santri, 'Penempatan', null, $santri->kelas, 'Penempatan awal santri.');
        }

        return back()->with('success', 'Data santri berhasil ditambahkan.');
    }

    public function update(Request $request, Santri $santri): RedirectResponse
    {
        $data = $this->validateData($request, $santri);

        if ($newPhoto = $this->handlePhoto($request)) {
            if ($santri->photo_path) {
                Storage::disk('public')->delete($santri->photo_path);
            }
            $data['photo_path'] = $newPhoto;
        }

        $oldKelas = $santri->kelas;
        $santri->update($data);

        // Rekam riwayat bila kelas berubah lewat form edit.
        if ($oldKelas?->id !== $santri->kelas_id) {
            $santri->refresh()->load('kelas');
            ClassHistory::record($santri, 'Pindah Kelas', $oldKelas, $santri->kelas, 'Perubahan kelas via edit santri.');
        }

        return back()->with('success', 'Data santri berhasil diperbarui.');
    }

    public function destroy(Santri $santri): RedirectResponse
    {
        if ($santri->photo_path) {
            Storage::disk('public')->delete($santri->photo_path);
        }
        $santri->delete();

        return back()->with('success', 'Data santri berhasil dihapus.');
    }

    public function regenerateCard(Santri $santri): RedirectResponse
    {
        $santri->update(['card_token' => Santri::generateCardToken()]);

        return back()->with('success', 'Token kartu santri berhasil diperbarui.');
    }

    /**
     * Halaman cetak banyak kartu sekaligus (print -> PDF).
     */
    public function bulkCards(Request $request): View
    {
        $ids = $this->validatedIds($request);
        $santri = Santri::with('kelas')->whereIn('id', $ids)->orderBy('name')->get();

        abort_if($santri->isEmpty(), 404, 'Tidak ada santri yang dipilih.');

        return view('santri.cards-bulk', compact('santri'));
    }

    /**
     * Regenerasi token kartu untuk banyak santri sekaligus.
     */
    public function bulkRegenerate(Request $request): RedirectResponse
    {
        $ids = $this->validatedIds($request);
        $count = 0;

        foreach (Santri::whereIn('id', $ids)->get() as $santri) {
            $santri->update(['card_token' => Santri::generateCardToken()]);
            $count++;
        }

        return back()->with('success', "{$count} token kartu santri berhasil diperbarui.");
    }

    /**
     * Pindahkan banyak santri terpilih ke satu kelas tujuan sekaligus.
     */
    public function bulkMoveClass(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:santris,id'],
            'kelas_id' => ['nullable', 'exists:kelas,id'],
        ]);

        $target = $validated['kelas_id'] ? Kelas::find($validated['kelas_id']) : null;
        $count = 0;

        foreach (Santri::with('kelas')->whereIn('id', $validated['ids'])->get() as $santri) {
            $from = $santri->kelas;
            if ($from?->id === $target?->id) {
                continue; // sudah di kelas tujuan
            }
            $santri->update(['kelas_id' => $target?->id]);
            ClassHistory::record($santri, $target ? 'Pindah Kelas' : 'Keluar', $from, $target, 'Pemindahan massal dari Data Santri.');
            $count++;
        }

        $label = $target ? "ke kelas {$target->name}" : 'keluar dari kelas';
        return back()->with('success', "{$count} santri berhasil dipindahkan {$label}.");
    }

    /**
     * Ekspor data santri terpilih ke file Excel (.xlsx) yang rapi & terformat.
     */
    public function export(Request $request): StreamedResponse
    {
        $ids = $this->validatedIds($request);
        $santri = Santri::with('kelas')->whereIn('id', $ids)->orderBy('name')->get();

        $no = 0;
        $rows = $santri->map(fn ($s) => [
            ++$no,
            $s->nis,
            $s->nisn,
            $s->name,
            $s->gender === 'L' ? 'Laki-laki' : ($s->gender === 'P' ? 'Perempuan' : '-'),
            $s->kelas?->name ?? '-',
            $s->status,
            $s->birth_place,
            $s->birth_date?->format('d-m-Y'),
            $s->address,
            $s->wali_name,
            $s->wali_phone,
            $s->card_token,
        ]);

        return ExcelExporter::download(
            'DATA SANTRI — '.(Branding::data()['pondok_name'] ?? 'PPTQ'),
            ['No', 'NIS', 'NISN', 'Nama', 'Jenis Kelamin', 'Kelas', 'Status', 'Tempat Lahir', 'Tanggal Lahir', 'Alamat', 'Nama Wali', 'HP Wali', 'Token Kartu'],
            $rows,
            [
                'sheetTitle' => 'Data Santri',
                'subtitle' => 'Dicetak: '.now()->format('d-m-Y H:i').' • Total: '.$santri->count().' santri',
                'filename' => 'data-santri-'.now()->format('Ymd-His').'.xlsx',
                'text' => ['B', 'C', 'L'],
                'center' => ['A', 'F', 'G'],
            ],
        );
    }

    /**
     * @return array<int, int>
     */
    private function validatedIds(Request $request): array
    {
        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:santris,id'],
        ]);

        return $validated['ids'];
    }

    /**
     * @return array<string, mixed>
     */
    private function validateData(Request $request, ?Santri $santri = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'nis' => ['required', 'string', 'max:30', Rule::unique('santris')->ignore($santri?->id)],
            'nisn' => ['nullable', 'string', 'max:30'],
            'birth_place' => ['nullable', 'string', 'max:100'],
            'birth_date' => ['nullable', 'date'],
            'gender' => ['nullable', 'in:L,P'],
            'kelas_id' => ['nullable', 'exists:kelas,id'],
            'status' => ['required', 'in:Aktif,Lulus,Pindah,Keluar,Nonaktif'],
            'address' => ['nullable', 'string', 'max:500'],
            'wali_name' => ['nullable', 'string', 'max:150'],
            'wali_phone' => ['nullable', 'string', 'max:30'],
            'wali_relation' => ['nullable', 'string', 'max:50'],
        ]);
    }

    private function handlePhoto(Request $request): ?string
    {
        if (! $request->hasFile('photo')) {
            return null;
        }
        $request->validate(['photo' => ['image', 'max:2048']]);

        return $request->file('photo')->store('santri-photos', 'public');
    }
}
