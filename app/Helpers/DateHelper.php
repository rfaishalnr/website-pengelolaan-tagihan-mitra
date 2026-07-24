<?php

namespace App\Helpers;

use Carbon\Carbon;

class DateHelper
{
    public static function formatTanggalLengkap($tanggal)
    {
        if (empty($tanggal)) {
            return null;
        }

        $carbon = Carbon::parse($tanggal);

        return [
            // Tanggal dan waktu dasar
            'tanggal'           => $carbon->day, // Angka, contoh: 19
            'bulan'             => strtoupper($carbon->translatedFormat('F')), // JUNI
            'tahun'             => $carbon->year, // 2025
            'hari'              => strtoupper($carbon->translatedFormat('l')), // KAMIS

            // Terbilang
            'tanggal_terbilang' => strtoupper(self::terbilang($carbon->day)), // SEMBILAN BELAS
            'tahun_terbilang'   => strtoupper(self::terbilang($carbon->year)), // DUA RIBU DUA PULUH LIMA

            // Format khusus
            'format_tanggal_slash' => $carbon->format('d/m/Y'), // 19/06/2025
        ];
    }

    public static function terbilang($number)
    {
        $formatter = new \NumberFormatter('id_ID', \NumberFormatter::SPELLOUT);
        return $formatter->format($number);
    }
}