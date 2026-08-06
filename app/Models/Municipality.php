<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Municipality extends Model
{
    use HasFactory;

    protected $fillable = [
        'cve_ent',
        'cve_mun',
        'nomgeo',
        'pob_total',
    ];

    public function state()
    {
        return $this->belongsTo(State::class, 'cve_ent', 'cve_ent');
    }
}
