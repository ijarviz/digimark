<?php

if (! function_exists('format_compact_number')) {
    /**
     * Formats a count the way TikTok's own UI does: below 10,000 shown
     * exactly as-is (e.g. comments: 2777 stays "2777"), otherwise divided
     * into K/M/B with one decimal place (trailing ".0" dropped) — e.g.
     * 1100000 -> "1.1M", 76800 -> "76.8K".
     */
    function format_compact_number(int|float|null $num): string
    {
        if ($num === null) {
            return '--';
        }

        $num = (float) $num;
        $abs = abs($num);

        if ($abs < 10_000) {
            return (string) (int) $num;
        }

        $units = [
            1_000_000_000 => 'B',
            1_000_000     => 'M',
            1_000         => 'K',
        ];

        foreach ($units as $threshold => $suffix) {
            if ($abs >= $threshold) {
                $value = $num / $threshold;

                return rtrim(rtrim(number_format($value, 1), '0'), '.') . $suffix;
            }
        }

        return (string) (int) $num;
    }
}
