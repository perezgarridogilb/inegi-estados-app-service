<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StateResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'cvegeo' => $this->cvegeo,
            'cve_ent' => $this->cve_ent,
            'nomgeo' => $this->nomgeo,
            'nom_abrev' => $this->nom_abrev,
            'pob_total' => $this->pob_total,
            'pob_femenina' => $this->pob_femenina,
            'pob_masculina' => $this->pob_masculina,
            'total_viviendas_habitadas' => $this->total_viviendas_habitadas,
        ];
    }
}
