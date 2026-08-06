<?php

namespace Database\Seeders;

use App\Models\State;
use Illuminate\Database\Seeder;

class StateSeeder extends Seeder
{
    public function run(): void
    {
        $states = [
            [
                'cvegeo' => '01',
                'cve_ent' => '01',
                'nomgeo' => 'Aguascalientes',
                'nom_abrev' => 'Ags.',
                'pob_total' => 1_425_607,
                'pob_femenina' => 725_431,
                'pob_masculina' => 700_176,
                'total_viviendas_habitadas' => 386_659,
            ],
            [
                'cvegeo' => '02',
                'cve_ent' => '02',
                'nomgeo' => 'Baja California',
                'nom_abrev' => 'B.C.',
                'pob_total' => 3_769_020,
                'pob_femenina' => 1_883_033,
                'pob_masculina' => 1_885_987,
                'total_viviendas_habitadas' => 1_070_097,
            ],
            [
                'cvegeo' => '03',
                'cve_ent' => '03',
                'nomgeo' => 'Baja California Sur',
                'nom_abrev' => 'B.C.S.',
                'pob_total' => 798_447,
                'pob_femenina' => 397_117,
                'pob_masculina' => 401_330,
                'total_viviendas_habitadas' => 257_189,
            ],
            [
                'cvegeo' => '04',
                'cve_ent' => '04',
                'nomgeo' => 'Campeche',
                'nom_abrev' => 'Camp.',
                'pob_total' => 928_363,
                'pob_femenina' => 468_740,
                'pob_masculina' => 459_623,
                'total_viviendas_habitadas' => 235_594,
            ],
            [
                'cvegeo' => '05',
                'cve_ent' => '05',
                'nomgeo' => 'Coahuila de Zaragoza',
                'nom_abrev' => 'Coah.',
                'pob_total' => 3_146_771,
                'pob_femenina' => 1_591_250,
                'pob_masculina' => 1_555_521,
                'total_viviendas_habitadas' => 864_884,
            ],
            [
                'cvegeo' => '06',
                'cve_ent' => '06',
                'nomgeo' => 'Colima',
                'nom_abrev' => 'Col.',
                'pob_total' => 731_391,
                'pob_femenina' => 369_128,
                'pob_masculina' => 362_263,
                'total_viviendas_habitadas' => 223_808,
            ],
            [
                'cvegeo' => '07',
                'cve_ent' => '07',
                'nomgeo' => 'Chiapas',
                'nom_abrev' => 'Chis.',
                'pob_total' => 5_543_344,
                'pob_femenina' => 2_824_129,
                'pob_masculina' => 2_719_215,
                'total_viviendas_habitadas' => 1_400_550,
            ],
            [
                'cvegeo' => '08',
                'cve_ent' => '08',
                'nomgeo' => 'Chihuahua',
                'nom_abrev' => 'Chih.',
                'pob_total' => 3_741_869,
                'pob_femenina' => 1_889_464,
                'pob_masculina' => 1_852_405,
                'total_viviendas_habitadas' => 1_075_780,
            ],
            [
                'cvegeo' => '09',
                'cve_ent' => '09',
                'nomgeo' => 'Ciudad de México',
                'nom_abrev' => 'CDMX',
                'pob_total' => 9_209_944,
                'pob_femenina' => 4_800_032,
                'pob_masculina' => 4_409_912,
                'total_viviendas_habitadas' => 2_772_353,
            ],
            [
                'cvegeo' => '10',
                'cve_ent' => '10',
                'nomgeo' => 'Durango',
                'nom_abrev' => 'Dgo.',
                'pob_total' => 1_832_650,
                'pob_femenina' => 920_545,
                'pob_masculina' => 912_105,
                'total_viviendas_habitadas' => 493_178,
            ],
        ];

        foreach ($states as $state) {
            State::updateOrCreate(
                ['cve_ent' => $state['cve_ent']],
                $state
            );
        }
    }
}
