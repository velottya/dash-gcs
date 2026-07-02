<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RkapExport implements FromArray, WithStyles, WithColumnWidths, WithTitle
{
    private array $bulanNames = ['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
    private array $statusLabels = [
        'draft'       => 'Draft',
        'submitted'   => 'Menunggu GM',
        'gm_approved' => 'Menunggu Direksi',
        'gm_rejected' => 'Ditolak GM',
        'approved'    => 'Disahkan',
        'rejected'    => 'Ditolak Direksi',
    ];

    public function __construct(private array $data) {}

    public function title(): string
    {
        return 'RKAP';
    }

    public function array(): array
    {
        $rkap    = $this->data['rkap'];
        $details = $this->data['details'];

        $rows = [];

        // Header info
        $rows[] = ['RENCANA KERJA DAN ANGGARAN PERUSAHAAN (RKAP)'];
        $rows[] = ['PT Gresik Cipta Sejahtera'];
        $rows[] = [];
        $rows[] = ['Produk',  $rkap->PRODUK ?? '-'];
        $rows[] = ['Tahun',   $rkap->TAHUN  ?? '-'];
        $rows[] = ['Manager', $rkap->nama_manager ?? $rkap->NIK_MANAGER ?? '-'];
        $rows[] = ['Status',  $this->statusLabels[$rkap->STATUS] ?? $rkap->STATUS];
        $rows[] = [];

        // Table header
        $rows[] = ['Bulan', 'Qty (Ton)', 'Nilai (Rp)'];

        $totalQty   = 0;
        $totalNilai = 0;

        for ($b = 1; $b <= 12; $b++) {
            $d      = $details[$b] ?? null;
            $qty    = $d ? (float) $d->QTY_TON        : 0;
            $nilai  = $d ? (float) $d->NILAI_RUPIAH   : 0;
            $totalQty   += $qty;
            $totalNilai += $nilai;
            $rows[] = [$this->bulanNames[$b], $qty, $nilai];
        }

        $rows[] = ['TOTAL', $totalQty, $totalNilai];

        // Catatan
        if (! empty($rkap->CATATAN_GM)) {
            $rows[] = [];
            $rows[] = ['Catatan GM', $rkap->CATATAN_GM];
        }
        if (! empty($rkap->CATATAN_DIREKSI)) {
            $rows[] = [];
            $rows[] = ['Catatan Direksi', $rkap->CATATAN_DIREKSI];
        }

        return $rows;
    }

    public function styles(Worksheet $sheet): array
    {
        // Title rows
        $sheet->mergeCells('A1:C1');
        $sheet->mergeCells('A2:C2');

        // Table header row (row 9)
        $headerRow = 9;
        $lastRow   = $headerRow + 13; // 12 months + total

        // Number format for qty/nilai columns
        $sheet->getStyle("B{$headerRow}:C{$lastRow}")
            ->getNumberFormat()
            ->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

        return [
            1 => ['font' => ['bold' => true, 'size' => 14], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]],
            2 => ['font' => ['bold' => true], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]],
            $headerRow => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1D4520']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
            $lastRow => [
                'font' => ['bold' => true],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E8F5E9']],
            ],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 20,
            'B' => 18,
            'C' => 22,
        ];
    }
}
