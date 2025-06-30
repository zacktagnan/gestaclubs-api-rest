<?php

if (!function_exists('getCurrencyCodeForLocale')) {
    function getCurrencyCodeForLocale(string $locale): string
    {
        return match ($locale) {
            'es_ES' => 'EUR',
            'en_US' => 'USD',
            'en_GB' => 'GBP',
            'fr_FR' => 'EUR',
            'de_DE' => 'EUR',
            'ja_JP' => 'JPY',
            'pt_BR' => 'BRL',
            'zh_CN' => 'CNY',

            default => 'EUR', // Fallback
        };
    }
}

if (!function_exists('formatCurrencyLocalized')) {
    function formatCurrencyLocalized(float|int $amount, ?string $locale = null): string
    {
        $locale ??= app()->getLocale();
        $currency = getCurrencyCodeForLocale($locale);

        try {
            $formatter = new \NumberFormatter($locale, \NumberFormatter::CURRENCY);
        } catch (\ValueError $e) {
            // Locale inválido: en ese caso, se usará un fallback seguro
            // entre los locales conocidos en el proyecto.
            $fallbackLocale = 'es_ES';
            $currency = getCurrencyCodeForLocale($fallbackLocale);
            $formatter = new \NumberFormatter($fallbackLocale, \NumberFormatter::CURRENCY);
        }

        return $formatter->formatCurrency($amount, $currency);
    }
}
