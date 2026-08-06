<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\MunicipalityResource;
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
            $states = $this->inegiClient->getMunicipalities($key_state);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], Response::HTTP_BAD_GATEWAY);
        }

        return response()->json([
            'success' => true,
            'message' => "Se sincronizaron x municipios.",
            'data' => [
                'datos' => $states,
            ],
        ]);
    }
}
