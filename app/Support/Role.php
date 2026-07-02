<?php

namespace App\Support;

class Role
{
    const SUPERADMIN = 1;
    const GM         = 2;
    const MANAGER    = 3;
    const DIREKSI    = 4;

    const LABELS = [
        self::SUPERADMIN => 'Superadmin',
        self::GM         => 'General Manager',
        self::MANAGER    => 'Manager',
        self::DIREKSI    => 'Direksi',
    ];

    public static function label(int $level): string
    {
        return self::LABELS[$level] ?? 'Unknown';
    }

    public static function isSuperadmin(): bool
    {
        return (int) session('level') === self::SUPERADMIN;
    }

    public static function isGm(): bool
    {
        return (int) session('level') === self::GM;
    }

    public static function isManager(): bool
    {
        return (int) session('level') === self::MANAGER;
    }

    public static function isDireksi(): bool
    {
        return (int) session('level') === self::DIREKSI;
    }

    public static function current(): int
    {
        return (int) session('level');
    }
}
