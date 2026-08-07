<?php

namespace App\Services;

use App\Repositories\StatesRepository;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use stdClass;

class StateService
{
    public function __construct(
        protected InegiClient $inegiClient,
        protected StatesRepository $statesRepository,
    ) {}

    public function sync(): array
    {
        return $this->inegiClient->getStates();
    }

    public function viewData(): array
    {
        $table = new stdClass;
        $table->list = route('states.list');
        $table->title = 'Estados';
        $table->button = 'Sincronizar estados en el sistema';
        $table->question = '¿Desea insertar los estados en el sistema?';
        $table->js = 'states-table';
        $table->name = 'states-table';

        $routes = new stdClass;
        $routes->sync_save = route('states.save');
        $routes->sync = route('states.sync');
        $routes->delete = route('states.delete');

        return compact('table', 'routes');
    }

    public function save(array $states): int
    {
        $count = 0;

        foreach ($states as $state) {
            try {
                DB::beginTransaction();
                $state = $this->statesRepository->query()
                    ->withTrashed()
                    ->updateOrCreate(
                        ['cve_ent' => $state['cve_ent']], // unique from ddl
                        [
                            'cvegeo' => $state['cvegeo'],
                            'nomgeo' => $state['nomgeo'],
                            'nom_abrev' => $state['nom_abrev'] ?? null,
                            'pob_total' => (int) $state['pob_total']
                        ]
                    );

                if ($state->trashed()) {
                    $state->restore(); // reset from softdeletes
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

    public function query(): stdClass
    {
        $query = $this->statesRepository->query()->select([
            'id',
            'cvegeo',
            'cve_ent',
            'nomgeo',
            'nom_abrev',
            'pob_total'
        ]);

        $data = new stdClass;
        $data->query = $query;
        $data->total = $query->count();
        return $data;
    }

    public function delete(): int
    {
        return $this->statesRepository->query()
            // ->where('cve_ent', $cve_ent)
            ->all()->each->delete();
    }
}
