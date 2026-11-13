<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Organizer;

use App\Service\Organizer\Siret\ApiCheckSiret;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

final class ApiCheckSiretTest extends TestCase
{
    public function testKnownSiretIsAccepted(): void
    {
        $client = new MockHttpClient(static function (string $method, string $url): MockResponse {
            self::assertSame('GET', $method);
            self::assertSame('https://api.insee.fr/api-sirene/3.11/siret/49098556100011', $url);

            return new MockResponse('{}', ['http_code' => 200]);
        });

        self::assertTrue((new ApiCheckSiret($client))->check('49098556100011'));
    }

    public function testUnknownSiretIsRejected(): void
    {
        $client = new MockHttpClient(new MockResponse('', ['http_code' => 404]));

        self::assertFalse((new ApiCheckSiret($client))->check('00000000000000'));
    }
}
