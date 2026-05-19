<?php

declare(strict_types=1);

namespace App\Tests\Unit\Tools;

use App\Tools\TagService;
use PHPUnit\Framework\TestCase;

final class TagServiceTest extends TestCase
{
    public function testCreateHtmlTagEscapesEventTitle(): void
    {
        $service = new TagService();

        $html = $service->createHtmlTag('ABCDE-2542', 12, '<script>alert(1)</script>');

        self::assertStringNotContainsString('<script>alert(1)</script>', $html);
        self::assertStringContainsString('&lt;script&gt;', $html);
    }

    public function testCreateHtmlTagEscapesQuotesInTitle(): void
    {
        $service = new TagService();

        $html = $service->createHtmlTag('CODE', 1, "x' onmouseover='alert(1)");

        self::assertStringNotContainsString("onmouseover='alert(1)'", $html);
    }

    public function testCreateHtmlTagHasNoHardcodedProductionDomain(): void
    {
        $service = new TagService();

        $html = $service->createHtmlTag('CODE', 1, 'Mon évènement');

        self::assertStringNotContainsString('herewego.aureliengirard.fr', $html);
    }

    public function testCodeHasRequestedLength(): void
    {
        self::assertSame(5, \strlen(TagService::code()));
        self::assertSame(8, \strlen(TagService::code(8)));
    }
}
