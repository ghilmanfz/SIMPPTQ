<?php

use Illuminate\Support\Carbon;

if (! function_exists('hari_indo')) {
    /**
     * Nama hari dalam Bahasa Indonesia (Senin..Ahad).
     */
    function hari_indo(Carbon|string|null $date): string
    {
        if ($date === null) {
            return '';
        }
        $date = $date instanceof Carbon ? $date : Carbon::parse($date);
        $days = ['Ahad', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];

        return $days[(int) $date->dayOfWeek];
    }
}

if (! function_exists('tgl')) {
    /**
     * Format tanggal ringkas: "07 Jun 2026" (opsional dengan nama hari).
     */
    function tgl(Carbon|string|null $date, bool $withDay = false): string
    {
        if (empty($date)) {
            return '-';
        }
        $date = $date instanceof Carbon ? $date : Carbon::parse($date);
        $months = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        $out = $date->format('d') . ' ' . $months[(int) $date->format('n')] . ' ' . $date->format('Y');

        return $withDay ? hari_indo($date) . ', ' . $out : $out;
    }
}

if (! function_exists('tgl_panjang')) {
    /**
     * Format tanggal panjang: "7 Juni 2026".
     */
    function tgl_panjang(Carbon|string|null $date): string
    {
        if (empty($date)) {
            return '-';
        }
        $date = $date instanceof Carbon ? $date : Carbon::parse($date);
        $months = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

        return (int) $date->format('j') . ' ' . $months[(int) $date->format('n')] . ' ' . $date->format('Y');
    }
}

if (! function_exists('jam')) {
    /**
     * Format jam HH:MM dari nilai waktu apa pun.
     */
    function jam(Carbon|string|null $time): string
    {
        if (empty($time)) {
            return '-';
        }
        $time = $time instanceof Carbon ? $time : Carbon::parse($time);

        return $time->format('H:i');
    }
}

if (! function_exists('rupiah')) {
    /**
     * Format mata uang Rupiah: "Rp 1.250.000".
     */
    function rupiah(int|float|string|null $number): string
    {
        return 'Rp ' . number_format((float) $number, 0, ',', '.');
    }
}
