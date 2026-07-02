<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
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

    /** @var int[] baris header tabel bulanan tiap produk (1-indexed) */
    private array $productHeaderRows = [];

    /** @var int[] baris subtotal tiap produk (1-indexed) */
    private array $subtotalRows = [];

    private int $grandTotalRow = 0;

    public function __construct(private array $data) {}

    public function title(): string
    {
        return 'RKAP';
    }

    public function array(): array
    {
        $rkap     = $this->data['rkap'];
        $products = $this->data['products'];

        $rows = [];

        // Header info
        $rows[] = ['RENCANA KERJA DAN ANGGARAN PERUSAHAAN (RKAP)'];
        $rows[] = ['PT Gresik Cipta Sejahtera'];
        $rows[] = [];
        $rows[] = ['Tahun',         $rkap->TAHUN ?? '-'];
        $rows[] = ['Manager',       $rkap->nama_manager ?? $rkap->NIK_MANAGER ?? '-'];
        $rows[] = ['Jumlah Produk', count($products)];
        $rows[] = ['Status',        $this->statusLabels[$rkap->STATUS] ?? $rkap->STATUS];

        $grandQty   = 0;
        $grandNilai = 0;

        foreach ($products as $p) {
            $rows[] = [];
            $rows[] = ['Produk', $p['stockid'].' — '.$p['produk']];

            $this->productHeaderRows[] = count($rows) + 1;
            $rows[] = ['Bulan', 'Qty (Ton)', 'Nilai (Rp)'];

            $subQty   = 0;
            $subNilai = 0;
            for ($b = 1; $b <= 12; $b++) {
                $d     = $p['months'][$b] ?? null;
                $qty   = $d ? (float) $d->QTY_TON      : 0;
                $nilai = $d ? (float) $d->NILAI_RUPIAH : 0;
                $subQty   += $qty;
                $subNilai += $nilai;
                $rows[] = [$this->bulanNames[$b], $qty, $nilai];
            }

            $this->subtotalRows[] = count($rows) + 1;
            $rows[] = ['Subtotal', $subQty, $subNilai];

            $grandQty   += $subQty;
            $grandNilai += $subNilai;
        }

        $rows[] = [];
        $this->grandTotalRow = count($rows) + 1;
        $rows[] = ['TOTAL KESELURUHAN', $grandQty, $grandNilai];

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

        // Number format for qty/nilai columns, seluruh baris tabel
        $lastRow = max([$this->grandTotalRow, ...$this->subtotalRows, 1]);
        $sheet->getStyle("B4:C{$lastRow}")
            ->getNumberFormat()
            ->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

        $styles = [
            1 => ['font' => ['bold' => true, 'size' => 14], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]],
            2 => ['font' => ['bold' => true], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]],
        ];

        foreach ($this->productHeaderRows as $row) {
            $styles[$row] = [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1D4520']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ];
        }

        foreach ($this->subtotalRows as $row) {
            $styles[$row] = [
                'font' => ['bold' => true],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E8F5E9']],
            ];
        }

        if ($this->grandTotalRow) {
            $styles[$this->grandTotalRow] = [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2F6C3F']],
            ];
        }

        return $styles;
    }

    public function columnWidths(): array
    {
        return [
            'A' => 24,
            'B' => 18,
            'C' => 22,
        ];
    }
}
