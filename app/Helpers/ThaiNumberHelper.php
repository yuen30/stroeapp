<?php

namespace App\Helpers;

class ThaiNumberHelper
{
    public static function toThaiText(float $number): string
    {
        $number = round($number, 2);
        $baht = floor($number);
        $satang = round(($number - $baht) * 100);

        if ($baht == 0 && $satang == 0) {
            return 'ศูนย์บาทถ้วน';
        }

        $text = '';

        if ($baht > 0) {
            $text .= self::numToThai($baht) . 'บาท';
        }

        if ($satang > 0) {
            $text .= self::numToThai($satang) . 'สตางค์';
        } else {
            $text .= 'ถ้วน';
        }

        return $text;
    }

    private static function numToThai(string $num): string
    {
        $num = ltrim($num, '0');
        if ($num == '') {
            return '';
        }

        // Handle millions by splitting
        if (strlen($num) > 6) {
            $millions = substr($num, 0, -6);
            $rest = substr($num, -6);
            $restText = self::numToThai($rest);
            return self::numToThai($millions) . 'ล้าน' . $restText;
        }

        $units = ['', 'สิบ', 'ร้อย', 'พัน', 'หมื่น', 'แสน'];
        $digits = ['ศูนย์', 'หนึ่ง', 'สอง', 'สาม', 'สี่', 'ห้า', 'หก', 'เจ็ด', 'แปด', 'เก้า'];

        $text = '';
        $len = strlen($num);

        for ($i = 0; $i < $len; $i++) {
            $digit = (int) $num[$i];
            $pos = $len - $i - 1;

            if ($digit == 0) {
                continue;
            }

            if ($pos == 1 && $digit == 1) { // 10
                $text .= 'สิบ';
            } elseif ($pos == 1 && $digit == 2) { // 20
                $text .= 'ยี่สิบ';
            } elseif ($pos == 0 && $digit == 1 && $len > 1) { // Ends with 1 (e.g., 21, 101)
                $text .= 'เอ็ด';
            } else {
                $text .= $digits[$digit] . $units[$pos];
            }
        }

        return $text;
    }

    public static function numberToThaiText(float $number): string
    {
        return self::toThaiText($number);
    }
}
