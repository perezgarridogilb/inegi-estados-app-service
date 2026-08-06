<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\MunicipalityResource;
use App\Models\Municipality;
use App\Models\State;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use stdClass;

class MunicipalityController extends Controller
{
    public function show(State $state)
    {
        $table = new stdClass;
        $table->list = route('municipality.list', ['supplierCode' => $state->cve_ent]);
        $table->js = 'municipality-table';
        $table->name = 'municipality-table';

        $routes = new stdClass;
        // $routes->sync_save = route('states.save');
        // $routes->sync = route('states.sync');

        return view('pages.municipality', compact('table', 'routes', 'state'));
    }

    public function list(Request $request)
    {
        $show_url = route('municipality.show') . '/';
        // $raw_show = DB::raw('CONCAT("' . $show_url . '", states.id) AS show_url');
        $raw_show = DB::raw("CONCAT('{$show_url}', municipalities.id) AS show_url");
        $municipalities = Municipality::select([
            'id',
            'cve_ent',
            'cve_mun',
            'nomgeo',
            'pob_total',
            $raw_show,
        ])
            ->when($request->filled('cve_ent'), fn ($query) => $query->where('cve_ent', $request->cve_ent))
            ->get();

        return response()->json([
            'data' => MunicipalityResource::collection($municipalities),
            'recordsTotal' => $municipalities->count(),
            'recordsFiltered' => $municipalities->count(),
        ]);
    }
}
