<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\StateResource;
use App\Models\State;
use App\Services\InegiClient;
use App\Services\StateService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use stdClass;
use Symfony\Component\HttpFoundation\Response;

class StateController extends Controller
{
    public function __construct(
        protected StateService $stateService,

        protected InegiClient $inegiClient,
    ) {}

    /**
     * getStatesData function
     *
     * @return View
     */
    public function getStatesData(): View
    {
        return view('pages.states', $this->stateService->viewData());
    }

    public function list(Request $request): JsonResponse
    {
        $show_url = route('municipality.show') . '/';
        $raw_show = DB::raw('CONCAT("' . $show_url . '", states.cve_ent) AS show_url');
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
            $raw_show
        ])->get();

        return response()->json([
            'data' => StateResource::collection($states),
            'recordsTotal' => $states->count(),
            'recordsFiltered' => $states->count(),
        ]);
    }

    public function sync(): JsonResponse
    {
        try {
            $states = $this->inegiClient->getStates();
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], Response::HTTP_BAD_GATEWAY);
        }
        $count = count(data_get($states, 'datos', []));
        return response()->json([
            'success' => true,
            'message' => "Se consultaron {$count} estados.",
            'data' => [
                'datos' => $states,
            ],
        ], Response::HTTP_OK);
    }

    /**
     * save function
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function save(Request $request): JsonResponse
    {
        $count = $this->stateService->save($request->input('states.datos'));

        return response()->json([
            'success' => true,
            'message' => "Se guardaron {$count} estados."
        ], Response::HTTP_OK);
    }

    public function setUpQuery()
    {
        $show_url = route('municipality.show') . '/';
        $raw_show = DB::raw('CONCAT("' . $show_url . '", states.cve_ent) AS show_url');        
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
            $raw_show
        ]);

        $data = (object)[];
        $data->query = $query;
        $data->total = $query->count();
        return $data;
    }

        public function delete(string $cve_ent = null): JsonResponse
    {
        $count = $this->stateService->delete();

        if ($count) {
            return response()->json([
                'success' => true,
                'message' => "Estados eliminados: {$count}",
            ], Response::HTTP_OK);
        }

        return response()->json([
            'success' => false,
            'message' => "No se encuentra el estado: {$cve_ent}.",
        ], Response::HTTP_NO_CONTENT);
    }
}
