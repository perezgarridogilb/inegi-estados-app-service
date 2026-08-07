<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class State extends Model
{
    use HasFactory, SoftDeletes;

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

    public function municipality()
    {
        return $this->hasMany(Municipality::class, 'cve_ent', 'cve_ent');
    }

    protected static function booted(): void
    {
        static::deleting(function (State $state) {
            $state->municipality()->delete();
        });
    }
}
