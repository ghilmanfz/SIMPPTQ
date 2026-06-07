<?php

namespace App\Support;

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Pembuat file Excel (.xlsx) yang rapi & konsisten untuk seluruh modul:
 * judul, sub-judul, header navy, border, zebra, auto-size, auto-filter, freeze pane.
 */
class ExcelExporter
{
    /**
     * @param  array<int, string>  $headers
     * @param  iterable<int, array<int, mixed>>  $rows
     * @param  array{
     *     sheetTitle?: string,
     *     subtitle?: string,
     *     text?: array<int, string>,
     *     center?: array<int, string>,
     *     money?: array<int, string>,
     *     filename?: string
     * }  $opts
     */
    public static function download(string $title, array $headers, iterable $rows, array $opts = []): StreamedResponse
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle(substr($opts['sheetTitle'] ?? 'Data', 0, 31));

        $colCount = count($headers);
        $lastCol = Coordinate::stringFromColumnIndex($colCount);

        // Baris 1: Judul
        $sheet->mergeCells("A1:{$lastCol}1");
        $sheet->setCellValue('A1', $title);
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14)->getColor()->setRGB('0B2265');
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Baris 2: Sub-judul
        $subtitle = $opts['subtitle'] ?? ('Dicetak: '.now()->format('d-m-Y H:i'));
        $sheet->mergeCells("A2:{$lastCol}2");
        $sheet->setCellValue('A2', $subtitle);
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A2')->getFont()->setItalic(true)->getColor()->setRGB('64748B');

        // Baris 4: Header tabel
        $headerRow = 4;
        $sheet->fromArray($headers, null, "A{$headerRow}");
        $headerRange = "A{$headerRow}:{$lastCol}{$headerRow}";
        $sheet->getStyle($headerRange)->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
        $sheet->getStyle($headerRange)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('0B2265');
        $sheet->getStyle($headerRange)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getRowDimension($headerRow)->setRowHeight(22);

        // Isi data
        $row = $headerRow + 1;
        $textCols = $opts['text'] ?? [];
        foreach ($rows as $data) {
            $sheet->fromArray(array_values($data), null, "A{$row}");
            foreach ($textCols as $col) {
                $cell = $sheet->getCell("{$col}{$row}");
                $cell->setValueExplicit((string) $cell->getValue(), DataType::TYPE_STRING);
            }
            $row++;
        }
        $lastRow = max($row - 1, $headerRow + 1);

        // Border seluruh tabel
        $sheet->getStyle("A{$headerRow}:{$lastCol}{$lastRow}")->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('CBD5E1');

        // Zebra striping
        for ($r = $headerRow + 1; $r <= $lastRow; $r++) {
            if (($r - $headerRow) % 2 === 0) {
                $sheet->getStyle("A{$r}:{$lastCol}{$r}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F1F5F9');
            }
        }

        // Format kolom uang (rupiah, rata kanan)
        foreach ($opts['money'] ?? [] as $col) {
            $sheet->getStyle("{$col}5:{$col}{$lastRow}")->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle("{$col}5:{$col}{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        }

        // Kolom rata tengah
        foreach ($opts['center'] ?? [] as $col) {
            $sheet->getStyle("{$col}5:{$col}{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }

        // Auto-size, filter, freeze
        for ($c = 1; $c <= $colCount; $c++) {
            $sheet->getColumnDimensionByColumn($c)->setAutoSize(true);
        }
        $sheet->setAutoFilter($headerRange);
        $sheet->freezePane("A{$headerRow}");

        $filename = $opts['filename'] ?? ('export-'.now()->format('Ymd-His').'.xlsx');

        return response()->streamDownload(function () use ($spreadsheet): void {
            (new Xlsx($spreadsheet))->save('php://output');
            $spreadsheet->disconnectWorksheets();
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}
