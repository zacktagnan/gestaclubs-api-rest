<?php

namespace Tests\Unit\API\V1\Libs;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Group;

#[Group('api:v1')]
#[Group('api:v1:unit')]
#[Group('api:v1:unit:libs')]
#[Group('api:v1:unit:libs:helpers')]
class HelpersTest extends TestCase
{
    #[Test]
    #[Group('api:v1:unit:libs:helpers:correct_currency_code')]
    public function it_returns_expected_currency_code_for_known_locales(): void
    {
        $this->assertSame('EUR', getCurrencyCodeForLocale('es_ES'));
        $this->assertSame('USD', getCurrencyCodeForLocale('en_US'));
        $this->assertSame('JPY', getCurrencyCodeForLocale('ja_JP'));
    }

    #[Test]
    #[Group('api:v1:unit:libs:helpers:default_currency_code')]
    public function it_returns_default_currency_for_unknown_locale(): void
    {
        $this->assertSame('EUR', getCurrencyCodeForLocale('xx_XX'));
    }

    #[Test]
    #[Group('api:v1:unit:libs:helpers:correct_format_currency')]
    public function it_formats_currency_based_on_locale(): void
    {
        $result = formatCurrencyLocalized(1000, 'en_US');
        $this->assertStringContainsString('$1,000.00', $result);

        $result = formatCurrencyLocalized(1000, 'es_ES');
        $this->assertStringContainsString('1.000', $result);
        $this->assertStringContainsString('€', $result);
    }

    #[Test]
    #[Group('api:v1:unit:libs:helpers:invalid_locale_gracefully')]
    public function it_handles_invalid_locale_gracefully(): void
    {
        $result = formatCurrencyLocalized(1234.56, 'xx_XX');

        // No debería lanzar excepción. Puede validarse que, simplemente, devuelve el fallback.
        // dd($result); // => 1.234,56\u{A0}€ que, código Unicode que, en HTML, equivale a 1.234,56&nbsp;€
        $this->assertIsString($result);
        $this->assertNotEmpty($result);
    }

    #[Test]
    #[Group('api:v1:unit:libs:helpers:amount_is_not_numeric')]
    public function it_fails_gracefully_when_amount_is_not_numeric(): void
    {
        $this->expectException(\TypeError::class); // solo si se quiere que explote

        formatCurrencyLocalized('not-a-number', 'en_US');
    }
}
