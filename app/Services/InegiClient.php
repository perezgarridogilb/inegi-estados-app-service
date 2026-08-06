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

    /**
     * getStates function
     *
     * Gets the states of Mexico from the INEGI service.
     *
     * @return array
     */
    public function getStates(): array
    {
        $response = Http::timeout(30)->get($this->baseUrl . '/mgee/');

        if ($response->failed()) {
            throw new \Exception('No se pudo consultar el servicio de INEGI.');
        }

        return $response->json();
    }

    /**
     * getMunicipalities function
     *
     * Gets the municipalities of a state from the INEGI service.
     *
     * @param string $key_state The state key
     * @return array
     */
    public function getMunicipalities(string $key_state): array
    {
        $response = Http::timeout(30)->get($this->baseUrl . '/mgem/' . $key_state);

        if ($response->failed()) {
            throw new \Exception('No se pudo consultar el servicio de INEGI.');
        }

        return $response->json();
    }
}
