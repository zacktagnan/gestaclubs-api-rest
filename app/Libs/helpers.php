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

        $formatter = new \NumberFormatter($locale, \NumberFormatter::CURRENCY);

        return $formatter->formatCurrency($amount, $currency);
    }
}
