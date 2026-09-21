<?php

if (!function_exists('terbilang')) {
    function terbilang($number): string
    {
        $number = abs((float)$number);
        $huruf = ["", "Satu", "Dua", "Tiga", "Empat", "Lima", "Enam", "Tujuh", "Delapan", "Sembilan", "Sepuluh", "Sebelas"];
        $hasil = "";

        if ($number < 12) {
            $hasil = " " . $huruf[(int)$number];
        } else if ($number < 20) {
            $hasil = terbilang($number - 10) . " Belas";
        } else if ($number < 100) {
            $hasil = terbilang($number / 10) . " Puluh" . terbilang($number % 10);
        } else if ($number < 200) {
            $hasil = " Seratus" . terbilang($number - 100);
        } else if ($number < 1000) {
            $hasil = terbilang($number / 100) . " Ratus" . terbilang($number % 100);
        } else if ($number < 2000) {
            $hasil = " Seribu" . terbilang($number - 1000);
        } else if ($number < 1000000) {
            $hasil = terbilang($number / 1000) . " Ribu" . terbilang($number % 1000);
        } else if ($number < 1000000000) {
            $hasil = terbilang($number / 1000000) . " Juta" . terbilang($number % 1000000);
        } else if ($number < 1000000000000) {
            $hasil = terbilang($number / 1000000000) . " Miliar" . terbilang(fmod($number, 1000000000));
        } else if ($number < 1000000000000000) {
            $hasil = terbilang($number / 1000000000000) . " Triliun" . terbilang(fmod($number, 1000000000000));
        }

        return trim($hasil);
    }
}
