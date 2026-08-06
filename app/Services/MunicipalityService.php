<?php

namespace App\Services;

use App\Models\State;
use App\Repositories\MunicipalitiesRepository;
use App\Repositories\StatesRepository;
use Illuminate\Support\Facades\DB;
use stdClass;

class MunicipalityService
{
    public function __construct(
        protected StatesRepository $statesRepository,
        protected MunicipalitiesRepository $municipalitiesRepository,
    ) {}

    public function viewData(string $cve_ent): array
    {
        $state = $this->statesRepository->findByCriteria(['cve_ent' => $cve_ent]);

        $table = new stdClass;
        $table->title = $state ? "Municipio por estado: {$state->nomgeo}" : "Municipios";
        $table->button = 'Sincronizar municipios en el sistema';
        $table->list = route('municipality.list', ['cve_ent' => $state->cve_ent]);
        $table->question = '¿Desea insertar los municipios por estado en el sistema?';
        $table->js = 'municipality-table';
        $table->name = 'municipality-table';

        $routes = new stdClass;
        $routes->sync = route('municipality.sync', ['key_state' => $state->cve_ent]);
        $routes->sync_save = null/* route('municipality.sync') */;

        return compact('table', 'routes', 'state');
    }

    public function listData($cve_ent)
    {
        $show_url = route('municipality.show') . '/';
        $raw_show = DB::raw("CONCAT('{$show_url}', municipalities.id) AS show_url");

        return $this->municipalitiesRepository->query()
            ->select([
                'id',
                'cve_ent',
                'cve_mun',
                'nomgeo',
                'pob_total',
                $raw_show,
            ])
            ->when($cve_ent, fn($query) => $query->where('cve_ent', $cve_ent))
            ->get();
    }
}
