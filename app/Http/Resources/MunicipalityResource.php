<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MunicipalityResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'cve_ent' => $this->cve_ent,
            'cve_mun' => $this->cve_mun,
            'nomgeo' => $this->nomgeo,
            'pob_total' => $this->pob_total,
            'show_url' => $this->show_url,
        ];
    }
}
