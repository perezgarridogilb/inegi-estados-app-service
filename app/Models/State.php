<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class State extends Model
{
    use HasFactory;

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    protected $fillable = [
        'cvegeo',
        'cve_ent',
        'nomgeo',
        'nom_abrev',
        'pob_total',
        'pob_femenina',
        'pob_masculina',
        'total_viviendas_habitadas',
    ];
}
