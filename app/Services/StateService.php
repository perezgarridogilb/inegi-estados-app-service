<?php

namespace App\Services;

use App\Repositories\StatesRepository;
use stdClass;

class StateService
{
    public function __construct(
        protected InegiClient $inegiClient,
        protected StatesRepository $statesRepository,
    ) {
    }

    public function sync(): array
    {
        return $this->inegiClient->getStates();
    }

    public function viewData(): array
    {
        $table = new stdClass;
        $table->list = route('states.list');
        $table->js = 'states-table';
        $table->name = 'states-table';

        $routes = new stdClass;
        $routes->sync_save = route('states.save');
        $routes->sync = route('states.sync');

        return compact('table', 'routes');
    }

    public function save(array $states): int
    {
        $count = 0;

        foreach ($states as $state) {
            $this->statesRepository->updateOrCreate(
                ['cve_ent' => $state['cve_ent']],
                [
                    'cvegeo' => $state['cvegeo'],
                    'nomgeo' => $state['nomgeo'],
                    'nom_abrev' => $state['nom_abrev'] ?? null,
                    'pob_total' => (int) $state['pob_total'],
                    'pob_femenina' => (int) $state['pob_femenina'],
                    'pob_masculina' => (int) $state['pob_masculina'],
                    'total_viviendas_habitadas' => (int) $state['total_viviendas_habitadas'],
                ]
            );
            $count++;
        }

        return $count;
    }

    public function query(): object
    {
        $query = $this->statesRepository->query()->select([
            'id',
            'cvegeo',
            'cve_ent',
            'nomgeo',
            'nom_abrev',
            'pob_total',
            'pob_femenina',
            'pob_masculina',
            'total_viviendas_habitadas',
        ]);

        $data = (object)[];
        $data->query = $query;
        $data->total = $query->count();
        return $data;
    }
}
