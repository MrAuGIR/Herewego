<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\User;

use App\Service\User\AvatarCatalog;
use PHPUnit\Framework\TestCase;

final class AvatarCatalogTest extends TestCase
{
    private function catalog(): AvatarCatalog
    {
        // public/img/avatar/ contient 0.png .. 4.png
        return new AvatarCatalog(\dirname(__DIR__, 4));
    }

    public function testAcceptsExistingNumericAvatar(): void
    {
        self::assertTrue($this->catalog()->isValid('1'));
    }

    public function testRejectsPathTraversal(): void
    {
        $catalog = $this->catalog();

        self::assertFalse($catalog->isValid('../../../../etc/passwd'));
        self::assertFalse($catalog->isValid('..%2f..%2fetc'));
        self::assertFalse($catalog->isValid('1/../../config/secrets'));
    }

    public function testRejectsNonNumericOrUnknownId(): void
    {
        $catalog = $this->catalog();

        self::assertFalse($catalog->isValid('1abc'));
        self::assertFalse($catalog->isValid(''));
        self::assertFalse($catalog->isValid('9999'));
    }
}
