<?php

namespace App\Services;

use App\Models\State;
use App\Repositories\MunicipalitiesRepository;
use App\Repositories\StatesRepository;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use stdClass;

class MunicipalityService
{
    /**
     * MunicipalityService constructor
     *
     * @param StatesRepository $statesRepository
     * @param MunicipalitiesRepository $municipalitiesRepository
     */
    public function __construct(
        protected StatesRepository $statesRepository,
        protected MunicipalitiesRepository $municipalitiesRepository,
    ) {}

    /**
     * viewData function
     *
     * Builds the table and routes data for the municipalities view.
     *
     * @param string $cve_ent The state key
     * @return array
     */
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
        $routes->sync_save = route('municipality.save');
        $routes->delete = route('municipality.delete', ['cve_ent' => $state->cve_ent]);

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

    /**
     * save function
     *
     * Inserts or updates the municipalities for each state, one by one
     * inside a transaction, logging and skipping any that fail.
     *
     * @param array $municipalities The municipalities to save
     * @return int The number of municipalities saved
     */
    public function save(array $municipalities): int
    {
        $count = 0;

        foreach ($municipalities as $municipality) {
            try {
                DB::beginTransaction();

                $municipality = $this->municipalitiesRepository->query()
                    ->withTrashed()
                    ->updateOrCreate(
                        [
                            'cve_ent' => $municipality['cve_ent'],
                            'cve_mun' => $municipality['cve_mun'],
                        ],
                        [
                            'nomgeo' => $municipality['nomgeo'],
                            'pob_total' => isset($municipality['pob_total']) ? (int) $municipality['pob_total'] : null,
                        ]
                    );

                if ($municipality->trashed()) {
                    $municipality->restore();
                }

                $count++;
                DB::commit();
            } catch (Exception $e) {
                DB::rollBack();
                Log::error(self::class . "@save: {$e->getMessage()}");
                continue;
            }
        }

        return $count;
    }

    /**
     * delete function
     *
     * Soft deletes all the municipalities of a state.
     *
     * @param string $cve_ent The state key
     * @return int The number of municipalities deleted
     */
    public function delete(string $cve_ent): int
    {
        return $this->municipalitiesRepository->query()
            ->where('cve_ent', $cve_ent)
            ->delete();
    }
}
