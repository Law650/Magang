<?php

namespace App\Helpers;

class FormatHelper
{
    /**
     * Konversi nilai desimal menjadi format pecahan peran (putaran).
     * Contoh: 1.125 -> "1 1/8", 0.5 -> "1/2", 2.0 -> "2"
     * Mendukung pecahan /8: 1/8, 1/4, 3/8, 1/2, 5/8, 3/4, 7/8.
     */
    public static function putaran($value)
    {
        if (!is_numeric($value)) {
            return (string) $value;
        }

        $value = (float) $value;
        $integerPart = floor($value);
        $decimalPart = $value - $integerPart;

        // Toleransi error float
        if (abs($decimalPart) < 0.001) {
            return (string) $integerPart;
        }

        // Mapping pecahan 1/8
        $fractions = [
            0.125 => '1/8',
            0.25  => '1/4',
            0.375 => '3/8',
            0.5   => '1/2',
            0.625 => '5/8',
            0.75  => '3/4',
            0.875 => '7/8',
        ];

        // Cari pecahan terdekat
        $closestFractionStr = '';
        $minDiff = 1.0;
        foreach ($fractions as $dec => $str) {
            $diff = abs($decimalPart - $dec);
            if ($diff < $minDiff && $diff < 0.05) { // Threshold 0.05
                $minDiff = $diff;
                $closestFractionStr = $str;
            }
        }

        if (empty($closestFractionStr)) {
            // Jika tidak cocok dengan pecahan per-delapan, tampilkan desimal asli
            return (string) $value;
        }

        if ($integerPart > 0) {
            return "{$integerPart} {$closestFractionStr}";
        }

        return $closestFractionStr;
    }
}
