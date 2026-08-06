<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\StateResource;
use App\Models\State;
use App\Services\InegiClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use stdClass;
use Symfony\Component\HttpFoundation\Response;

class StateController extends Controller
{
    public function __construct(
        protected InegiClient $inegiClient,
    ) {
    }

    /**
     * getStatesData function
     *
     * @return View
     */
    public function getStatesData(): View
    {
        $table = new stdClass;
        $table->filters = tableSearch(['clients']);
        $table->list = route('budgets.list'); // o 'budgets.list' según corresponda
        $table->js = 'states-table';
        $table->name = 'states-table';

        $routes = new stdClass;
        $routes->sync_save = route('states.save');
        $routes->sync_save = route('states.sync');

        return view('states', compact('table', 'routes'));
    }

    public function list(Request $request) : JsonResponse
    {
        if ($request->has('draw')) {
            $filters = datatableFilters($request);
            $data = $this->setUpQuery();
            return response()->json(createDatatable($data->query, $filters, $data->total, 'Listado', intval($filters->length)), 200);
        }

        $states = State::select([
            'id',
            'cvegeo',
            'cve_ent',
            'nomgeo',
            'nom_abrev',
            'pob_total',
            'pob_femenina',
            'pob_masculina',
            'total_viviendas_habitadas',
        ])->get();

        return response()->json([
            'data' => StateResource::collection($states),
            'recordsTotal' => $states->count(),
            'recordsFiltered' => $states->count(),
        ]);
    }

    public function sync() : JsonResponse
    {
        try {
            $states = $this->inegiClient->getStates();
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], Response::HTTP_BAD_GATEWAY);
        }

        // $count = 0;

        // foreach ($states as $state) {
        //     State::updateOrCreate(
        //         ['cve_ent' => $state['cve_ent']],
        //         [
        //             'cvegeo' => $state['cvegeo'],
        //             'nomgeo' => $state['nomgeo'],
        //             'nom_abrev' => $state['nom_abrev'] ?? null,
        //             'pob_total' => (int) $state['pob_total'],
        //             'pob_femenina' => (int) $state['pob_femenina'],
        //             'pob_masculina' => (int) $state['pob_masculina'],
        //             'total_viviendas_habitadas' => (int) $state['total_viviendas_habitadas'],
        //         ]
        //     );
        //     $count++;
        // }

        return response()->json([
            'success' => true,
            'message' => "Se sincronizaron x estados.",
            'data' => [
                'datos' => $states,
            ],
        ]);
    }

    /**
     * save function
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function save(Request $request) : JsonResponse
    {
        $count = 0;

        foreach ($this->inegiClient->getStates() as $state) {
            State::updateOrCreate(
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

        return response()->json([
            'success' => true,
            'message' => "Se guardaron {$count} estados.",
            'count' => $count,
        ]);
    }

    public function setUpQuery()
    {
        $query = State::select([
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
