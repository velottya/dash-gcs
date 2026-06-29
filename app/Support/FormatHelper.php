<?php

namespace App\Support;

class FormatHelper
{
    /**
     * Achievement percentage of realisasi against target (ported from
     * format_helper.php::targetPersen).
     */
    public static function targetPersen(int|float $target, int|float $realisasi): int
    {
        if ($target == 0) {
            $capai = $realisasi == 0 ? 0 : 100;
        } else {
            $capai = ($realisasi / $target) * 100;
        }

        return (int) $capai;
    }

    /**
     * Current accounting period (YYYYmm), offset 5 days back like the
     * legacy app to account for posting lag (format_helper.php::periodeTrans).
     */
    public static function periodeTrans(): string
    {
        return now()->subDays(5)->format('Ym');
    }

    /**
     * Scale rupiah values down to thousands (format_helper.php::redenominasi).
     */
    public static function redenominasi(int|float $val): int
    {
        return (int) ($val / 1000);
    }

    /**
     * Scale rupiah values down to thousands, keeping decimals (format_helper.php::redenominasi_fl).
     */
    public static function redenominasiFloat(int|float $val): string
    {
        return number_format($val / 1000, 2, '.', '');
    }

    public static function maskRp(int|float $val): string
    {
        return number_format((float) $val, 2, ',', '.');
    }

    public static function mask(int|float $val): string
    {
        return number_format((float) $val, 0, ',', '.');
    }

    /**
     * Bootstrap progress-bar color tier for an achievement percentage
     * (format_helper.php::barColorPos).
     */
    public static function barColorPos(int|float $val): string
    {
        if ($val < 75) {
            return 'bg-red-500';
        }

        if ($val <= 90) {
            return 'bg-amber-500';
        }

        return 'bg-green';
    }

    /**
     * Progress-bar color tier that flips thresholds depending on whether the
     * base value is a positive gain or a cost/expense (format_helper.php::barColor).
     */
    public static function barColor(int|float $nilai1, int|float $nilai): string
    {
        if ($nilai1 > 0) {
            return self::barColorPos($nilai);
        }

        if ($nilai > 100) {
            return 'bg-red-500';
        }

        if ($nilai >= 90) {
            return 'bg-amber-500';
        }

        return 'bg-green';
    }

    private const BULAN = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
    ];

    /**
     * Format a YYYYmm period string as "<Bulan> <Tahun>" (format_helper.php::getPeriodeTrans).
     */
    public static function getPeriodeTrans(string $periode): string
    {
        $bulan = (int) substr($periode, 4, 2);
        $tahun = substr($periode, 0, 4);

        return self::BULAN[$bulan].' '.$tahun;
    }

    /**
     * Indonesian month name for a 1-12 month number (format_helper.php::getBulan).
     */
    public static function getBulan(int $bulan): string
    {
        return self::BULAN[$bulan] ?? '';
    }

    /**
     * @return array<int, string>
     */
    public static function bulanList(): array
    {
        return self::BULAN;
    }
}
