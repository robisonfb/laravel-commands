<?php

use Carbon\Carbon;

if (!function_exists('parseDate')) {
    /**
     * Converte uma string ou DateTime para objeto Carbon.
     *
     * @param  mixed  $date
     * @return Carbon
     */
    function parseDate($date)
    {
        if ($date instanceof Carbon) {
            return $date;
        }

        if ($date instanceof \DateTime) {
            return Carbon::instance($date);
        }

        return Carbon::parse($date);
    }
}

if (!function_exists('format_date')) {
    /**
     * Formatar data apenas com dia, mês e ano.
     *
     * @param  mixed       $date
     * @param  string      $format
     * @return string|null
     */
    function format_date($date)
    {
        if (!$date) {
            return null;
        }

        $format = env('DATE_FORMAT', 'd/m/Y');

        return parseDate($date)->format($format);
    }
}

if (!function_exists('format_datetime')) {
    /**
     * Formatar data com dia, mês, ano, hora e minuto.
     *
     * @param  mixed       $date
     * @param  string      $format
     * @return string|null
     */
    function format_datetime($date)
    {
        if (!$date) {
            return null;
        }

        $format = env('DATETIME_FORMAT', 'd/m/Y H:i');

        return parseDate($date)->format($format);
    }
}

if (!function_exists('format_time')) {
    /**
     * Formatar apenas hora e minuto.
     *
     * @param  mixed       $date
     * @param  string      $format
     * @return string|null
     */
    function format_time($date, $format = 'H:i')
    {
        if (!$date) {
            return null;
        }

        return parseDate($date)->format($format);
    }
}

if (!function_exists('format_human')) {
    /**
     * Formatar data em formato relativo (há X tempo).
     *
     * @param  mixed       $date
     * @return string|null
     */
    function format_human($date)
    {
        if (!$date) {
            return null;
        }

        return parseDate($date)->diffForHumans();
    }
}

if (!function_exists('format_date_localized')) {
    /**
     * Formatar data no formato local, como "16. März 2025".
     *
     * @param  mixed       $date
     * @param  string      $locale
     * @return string|null
     */
    function format_date_localized($date)
    {
        if (!$date) {
            return null;
        }

        $locale     = env('APP_LOCALE') . '.UTF-8';
        $carbonDate = parseDate($date);

        // Salvar o locale atual
        $currentLocale = setlocale(LC_TIME, 0);

        // Definir o locale desejado
        setlocale(LC_TIME, $locale);

        // Formatar a data
        $formattedDate = strftime('%d. %B %Y', $carbonDate->timestamp);

        // Restaurar o locale original
        setlocale(LC_TIME, $currentLocale);

        return $formattedDate;
    }
}

function formatCurrency(float $amount): string
{
    $symbol   = env('CURRENCY_SYMBOL', '€');
    $position = env('CURRENCY_POSITION', 'after');

    $formatted = number_format($amount, 2, ',', '.');

    return $position === 'before'
        ? "{$symbol} {$formatted}"
        : "{$formatted} {$symbol}";
}
