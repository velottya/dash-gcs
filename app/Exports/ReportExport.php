<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ReportExport implements FromArray, WithStyles, WithColumnWidths, WithTitle
{
    public function __construct(
        private string $reportTitle,
        private array  $headers,
        private array  $rows,
        private ?int   $tahun     = null,
        private ?int   $bulan     = null,
        private bool   $prognosa  = false,
        private ?float $margin    = null,
    ) {}

    public function title(): string
    {
        return substr($this->reportTitle, 0, 31); // Excel sheet name max 31 chars
    }

    public function array(): array
    {
        $bulanNames = ['','Jan','Feb','Mar','Apr','Mei','Jun','Jul','Ags','Sep','Okt','Nov','Des'];
        $now = now();

        $data = [];
        $data[] = [strtoupper($this->reportTitle)];
        $data[] = ['PT Gresik Cipta Sejahtera'];

        $periodLabel = $this->tahun ? "Tahun {$this->tahun}" : '';
        if ($this->bulan) {
            $periodLabel .= " — " . ($bulanNames[$this->bulan] ?? "Bulan {$this->bulan}");
        }
        if ($periodLabel) {
            $data[] = [$periodLabel];
        }

        if ($this->prognosa) {
            $data[] = ['* Data tahun berjalan — termasuk prognosa tahunan' . ($this->margin !== null ? " (margin: {$this->margin}%)" : '')];
        }

        $data[] = [];
        $data[] = $this->headers;

        foreach ($this->rows as $row) {
            $data[] = $row;
        }

        return $data;
    }

    public function styles(Worksheet $sheet): array
    {
        $headerRowNum = $this->prognosa ? 7 : 6;

        return [
            1 => ['font' => ['bold' => true, 'size' => 13]],
            $headerRowNum => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1D4520']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
        ];
    }

    public function columnWidths(): array
    {
        $widths = [];
        foreach (range('A', 'Z') as $i => $col) {
            $widths[$col] = 18;
            if ($i >= count($this->headers) - 1) break;
        }
        return $widths;
    }
}
