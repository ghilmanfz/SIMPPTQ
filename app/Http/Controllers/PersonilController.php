<?php

namespace App\Http\Controllers;

use App\Models\Personil;
use App\Models\PersonilDocument;
use App\Models\User;
use App\Support\Branding;
use App\Support\ExcelExporter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\View\View;

class PersonilController extends Controller
{
    public function index(Request $request): View
    {
        $query = Personil::query()->with('user.role');

        if ($request->filled('fungsi')) {
            $query->where('fungsi_kerja', $request->fungsi);
        }
        if ($request->filled('status')) {
            $query->where('status_kerja', $request->status);
        }
        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(fn ($sub) => $sub->where('name', 'like', "%{$q}%")->orWhere('nik', 'like', "%{$q}%")->orWhere('jabatan', 'like', "%{$q}%"));
        }

        $personil = $query->orderBy('name')->paginate(10)->withQueryString();
        $canManage = auth()->user()->hasPermissionTo('personnel_manage');

        // Akun user yang belum terhubung ke personil (untuk form tautan).
        $availableUsers = User::doesntHave('personil')->orderBy('name')->get();

        return view('personil.index', compact('personil', 'canManage', 'availableUsers'));
    }

    /**
     * Ekspor data personil (mengikuti filter aktif) ke Excel.
     */
    public function export(Request $request): StreamedResponse
    {
        $query = Personil::query()->with('user.role');

        if ($request->filled('fungsi')) {
            $query->where('fungsi_kerja', $request->fungsi);
        }
        if ($request->filled('status')) {
            $query->where('status_kerja', $request->status);
        }
        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(fn ($sub) => $sub->where('name', 'like', "%{$q}%")->orWhere('nik', 'like', "%{$q}%")->orWhere('jabatan', 'like', "%{$q}%"));
        }

        $personil = $query->orderBy('name')->get();

        $no = 0;
        $rows = $personil->map(fn ($p) => [
            ++$no,
            $p->nik,
            $p->name,
            $p->gender === 'L' ? 'Laki-laki' : ($p->gender === 'P' ? 'Perempuan' : '-'),
            $p->jabatan,
            $p->unit_kerja,
            $p->fungsi_kerja,
            $p->status_kerja,
            $p->phone,
            $p->email,
            $p->is_active ? 'Aktif' : 'Nonaktif',
        ]);

        return ExcelExporter::download(
            'DATA PERSONIL — '.(Branding::data()['pondok_name'] ?? 'PPTQ'),
            ['No', 'NIK', 'Nama', 'Jenis Kelamin', 'Jabatan', 'Unit Kerja', 'Fungsi Kerja', 'Status Kerja', 'No. HP', 'Email', 'Status Akun'],
            $rows,
            [
                'sheetTitle' => 'Data Personil',
                'subtitle' => 'Dicetak: '.now()->format('d-m-Y H:i').' • Total: '.$personil->count().' personil',
                'filename' => 'data-personil-'.now()->format('Ymd-His').'.xlsx',
                'text' => ['B', 'I'],
                'center' => ['A', 'D', 'G', 'H', 'K'],
            ],
        );
    }

    public function show(Personil $personil): View
    {
        $personil->load('user.role', 'documents', 'jadwals.kelas', 'jadwals.mapel');
        $canManage = auth()->user()->hasPermissionTo('personnel_manage');

        return view('personil.show', compact('personil', 'canManage'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateData($request);
        $data['photo_path'] = $this->handlePhoto($request);

        Personil::create($data);

        return back()->with('success', 'Data personil berhasil ditambahkan.');
    }

    public function update(Request $request, Personil $personil): RedirectResponse
    {
        $data = $this->validateData($request, $personil);

        if ($newPhoto = $this->handlePhoto($request)) {
            if ($personil->photo_path) {
                Storage::disk('public')->delete($personil->photo_path);
            }
            $data['photo_path'] = $newPhoto;
        }

        $personil->update($data);

        return back()->with('success', 'Data personil berhasil diperbarui.');
    }

    public function destroy(Personil $personil): RedirectResponse
    {
        if ($personil->photo_path) {
            Storage::disk('public')->delete($personil->photo_path);
        }
        foreach ($personil->documents as $doc) {
            Storage::disk('local')->delete($doc->file_path);
        }
        $personil->delete();

        return back()->with('success', 'Data personil berhasil dihapus.');
    }

    public function storeDocument(Request $request, Personil $personil): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'file' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:4096'],
        ]);

        $file = $request->file('file');
        // Dokumen sensitif disimpan di disk privat (tidak dapat diakses publik).
        $path = $file->store('personil-documents', 'local');

        $personil->documents()->create([
            'name' => $request->name,
            'file_path' => $path,
            'mime' => $file->getClientMimeType(),
            'size' => $file->getSize(),
        ]);

        return back()->with('success', 'Dokumen berhasil diunggah.');
    }

    public function destroyDocument(PersonilDocument $document): RedirectResponse
    {
        Storage::disk('local')->delete($document->file_path);
        $document->delete();

        return back()->with('success', 'Dokumen berhasil dihapus.');
    }

    public function downloadDocument(PersonilDocument $document): StreamedResponse
    {
        abort_unless(Storage::disk('local')->exists($document->file_path), 404);

        return Storage::disk('local')->download($document->file_path, $document->name . '.' . pathinfo($document->file_path, PATHINFO_EXTENSION));
    }

    /**
     * @return array<string, mixed>
     */
    private function validateData(Request $request, ?Personil $personil = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'nik' => ['nullable', 'string', 'max:20', Rule::unique('personils')->ignore($personil?->id)],
            'gender' => ['nullable', 'in:L,P'],
            'birth_place' => ['nullable', 'string', 'max:100'],
            'birth_date' => ['nullable', 'date'],
            'address' => ['nullable', 'string', 'max:500'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'jabatan' => ['nullable', 'string', 'max:150'],
            'unit_kerja' => ['nullable', 'string', 'max:150'],
            'status_kerja' => ['required', 'string', 'max:50'],
            'fungsi_kerja' => ['required', 'in:Non-Pengajar,Pengajar,Dua Fungsi'],
            'salary_base' => ['nullable', 'numeric', 'min:0'],
            'salary_allowance' => ['nullable', 'numeric', 'min:0'],
            'salary_deduction' => ['nullable', 'numeric', 'min:0'],
            'user_id' => ['nullable', 'exists:users,id', Rule::unique('personils', 'user_id')->ignore($personil?->id)],
            'is_active' => ['nullable', 'boolean'],
        ]) + ['is_active' => $request->boolean('is_active')];
    }

    private function handlePhoto(Request $request): ?string
    {
        if (! $request->hasFile('photo')) {
            return null;
        }
        $request->validate(['photo' => ['image', 'max:2048']]);

        return $request->file('photo')->store('personil-photos', 'public');
    }
}
