<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\MunicipalitySaveRequest;
use App\Http\Resources\MunicipalityResource;
use App\Models\Municipality;
use App\Services\InegiClient;
use App\Services\MunicipalityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MunicipalityController extends Controller
{
    public function __construct(
        protected MunicipalityService $municipalityService,
        protected InegiClient $inegiClient,
    ) {}

    public function show($cve_ent)
    {
        return view('pages.municipality', $this->municipalityService->viewData($cve_ent));
    }

    public function list($cve_ent)
    {
        $municipalities = $this->municipalityService->listData($cve_ent);

        return response()->json([
            'data' => MunicipalityResource::collection($municipalities),
            'recordsTotal' => $municipalities->count(),
            'recordsFiltered' => $municipalities->count(),
        ]);
    }
    public function sync($key_state): JsonResponse
    {
        try {
            $municipalities = $this->inegiClient->getMunicipalities($key_state);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], Response::HTTP_BAD_GATEWAY);
        }
        $count = count(data_get($municipalities, 'datos', []));
        return response()->json([
            'success' => true,
            'message' => "Se consultaron {$count} municipios.",
            'data' => [
                'datos' => $municipalities,
            ],
        ]);
    }

        /**
     * save function
     *
     * @param MunicipalitySaveRequest $request
     * @return JsonResponse
     */
    public function save(MunicipalitySaveRequest $request): JsonResponse
    {
        $count = $this->municipalityService->save($request->input('municipalities'));

        return response()->json([
            'success' => true,
            'message' => "Se guardaron {$count} municipios.",
            'count' => $count,
        ]);
    }

    public function delete(?string $cve_ent): JsonResponse
    {
        $count = $this->municipalityService->delete($cve_ent);

        if ($count) {
            return response()->json([
                'success' => true,
                'message' => "Se eliminaron {$count} municipios.",
            ], Response::HTTP_OK);
        }

        return response()->json([
            'success' => false,
            'message' => 'No se encontraron municipios para eliminar.',
        ], Response::HTTP_NOT_FOUND);
    }
}
