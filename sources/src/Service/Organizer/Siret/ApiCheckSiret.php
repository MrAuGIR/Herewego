<?php

namespace App\Service\Organizer\Siret;

use Symfony\Component\HttpClient\CurlHttpClient;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class ApiCheckSiret
{
    private const API_URL = 'https://api.insee.fr/api-sirene/3.11/siret/';

    private HttpClientInterface $client;
    public function __construct()
    {
        $this->init();
    }

    private function init() : void
    {
        $this->client = new CurlHttpClient([
            'base_uri' => self::API_URL
        ]);
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function check(string $siret): bool
    {
        $code = $this->callApi($siret)->getStatusCode();

        return match ($code) {
            200, 201 => true,
            default => false,
        };

    }

    /**
     * @throws TransportExceptionInterface
     */
    private function callApi(string $siret): ResponseInterface
    {
        return $this->client->request('GET', $siret);
    }
}