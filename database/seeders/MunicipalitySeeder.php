<?php

namespace Database\Seeders;

use App\Models\Municipality;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;

class MunicipalitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $states = \App\Models\State::pluck('cve_ent')->all();

        foreach ($states as $cveEnt) {
            $response = Http::timeout(30)->get("https://gaia.inegi.org.mx/wscatgeo/v2/mgem/{$cveEnt}");

            if ($response->failed()) {
                continue;
            }

            foreach ($response->json('datos', []) as $mun) {
                Municipality::updateOrCreate(
                    ['cve_ent' => $mun['cve_ent'], 'cve_mun' => $mun['cve_mun']],
                    [
                        'nomgeo' => $mun['nomgeo'],
                        'pob_total' => (int) ($mun['pob_total'] ?? 0),
                    ]
                );
            }
        }
    }
}
