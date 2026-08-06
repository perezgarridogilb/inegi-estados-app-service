<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class InegiClient
{
    protected string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = env('INEGI_STATES');
    }

    public function getStates(): mixed
    {
        $response = Http::timeout(30)->get($this->baseUrl . '/mgee/');

        if ($response->failed()) {
            throw new \Exception('No se pudo consultar el servicio de INEGI.');
        }

        return $response->json();
    }

    public function getMunicipalities(string $key_state): mixed
    {
        $response = Http::timeout(30)->get($this->baseUrl . '/mgee/' . $key_state);

        if ($response->failed()) {
            throw new \Exception('No se pudo consultar el servicio de INEGI.');
        }

        return $response->json();
    }
}
