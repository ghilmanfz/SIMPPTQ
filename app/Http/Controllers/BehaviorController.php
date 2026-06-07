<?php

namespace App\Http\Controllers;

use App\Models\Behavior;
use App\Models\Santri;
use App\Support\Branding;
use App\Support\ExcelExporter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BehaviorController extends Controller
{
    public function index(Request $request): View
    {
        $behaviors = $this->filteredQuery($request)
            ->with('santri.kelas', 'recorder')
            ->latest('date')->latest('id')
            ->paginate(12)->withQueryString();

        $santriList = Santri::where('status', 'Aktif')->with('kelas')->orderBy('name')->get();

        // Ringkasan skor bila difilter per santri.
        $summary = null;
        if ($request->filled('santri_id')) {
            $base = Behavior::where('santri_id', $request->santri_id);
            $kebaikan = (clone $base)->where('type', 'Kebaikan')->sum('points');
            $pelanggaran = (clone $base)->where('type', 'Pelanggaran')->sum('points');
            $summary = [
                'santri' => Santri::with('kelas')->find($request->santri_id),
                'kebaikan' => (int) $kebaikan,
                'pelanggaran' => (int) $pelanggaran,
                'saldo' => (int) $kebaikan - (int) $pelanggaran,
            ];
        }

        return view('perilaku.index', compact('behaviors', 'santriList', 'summary'));
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

    /**
     * Rekap & peringkat poin perilaku santri pada rentang tanggal.
     */
    public function rekap(Request $request): View
    {
        [$start, $end] = $this->range($request);

        $ranking = Santri::query()
            ->select('santris.*')
            ->selectRaw("COALESCE(SUM(CASE WHEN behaviors.type = 'Kebaikan' THEN behaviors.points ELSE 0 END), 0) as kebaikan")
            ->selectRaw("COALESCE(SUM(CASE WHEN behaviors.type = 'Pelanggaran' THEN behaviors.points ELSE 0 END), 0) as pelanggaran")
            ->selectRaw("COALESCE(SUM(CASE WHEN behaviors.type = 'Kebaikan' THEN behaviors.points ELSE -behaviors.points END), 0) as saldo")
            ->join('behaviors', 'behaviors.santri_id', '=', 'santris.id')
            ->whereBetween('behaviors.date', [$start, $end])
            ->groupBy('santris.id')
            ->with('kelas')
            ->orderByDesc('saldo')
            ->get();

        $categories = Behavior::query()
            ->selectRaw("COALESCE(NULLIF(category, ''), '(Tanpa Kategori)') as kategori")
            ->selectRaw("SUM(CASE WHEN type = 'Kebaikan' THEN points ELSE 0 END) as kebaikan")
            ->selectRaw("SUM(CASE WHEN type = 'Pelanggaran' THEN points ELSE 0 END) as pelanggaran")
            ->selectRaw('COUNT(*) as jumlah')
            ->whereBetween('date', [$start, $end])
            ->groupByRaw("COALESCE(NULLIF(category, ''), '(Tanpa Kategori)')")
            ->orderByDesc(DB::raw('kebaikan + pelanggaran'))
            ->get();

        return view('perilaku.rekap', compact('ranking', 'categories', 'start', 'end'));
    }

    /**
     * Ekspor catatan perilaku (mengikuti filter aktif) ke Excel.
     */
    public function export(Request $request): StreamedResponse
    {
        $behaviors = $this->filteredQuery($request)
            ->with('santri.kelas', 'recorder')
            ->latest('date')->latest('id')->get();

        $no = 0;
        $rows = $behaviors->map(fn ($b) => [
            ++$no,
            $b->date?->format('d-m-Y'),
            $b->santri?->name ?? '-',
            $b->santri?->kelas?->name ?? '-',
            $b->type,
            $b->category,
            $b->signedPoints(),
            $b->note,
            $b->recorder?->name ?? '-',
        ]);

        return ExcelExporter::download(
            'CATATAN PERILAKU SANTRI — '.(Branding::data()['pondok_name'] ?? 'PPTQ'),
            ['No', 'Tanggal', 'Santri', 'Kelas', 'Jenis', 'Kategori', 'Poin', 'Keterangan', 'Pencatat'],
            $rows,
            [
                'sheetTitle' => 'Perilaku',
                'subtitle' => 'Dicetak: '.now()->format('d-m-Y H:i').' • Total: '.$behaviors->count().' catatan',
                'filename' => 'perilaku-santri-'.now()->format('Ymd-His').'.xlsx',
                'center' => ['A', 'B', 'E', 'G'],
            ],
        );
    }

    /**
     * Query catatan perilaku dengan filter dari request.
     */
    private function filteredQuery(Request $request)
    {
        $query = Behavior::query();

        if ($request->filled('santri_id')) {
            $query->where('santri_id', $request->santri_id);
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('start')) {
            $query->whereDate('date', '>=', $request->start);
        }
        if ($request->filled('end')) {
            $query->whereDate('date', '<=', $request->end);
        }

        return $query;
    }

    /**
     * @return array{0: \Illuminate\Support\Carbon, 1: \Illuminate\Support\Carbon}
     */
    private function range(Request $request): array
    {
        $start = $request->filled('start') ? Carbon::parse($request->start) : Carbon::today()->startOfMonth();
        $end = $request->filled('end') ? Carbon::parse($request->end) : Carbon::today()->endOfMonth();

        return [$start, $end];
    }
}
