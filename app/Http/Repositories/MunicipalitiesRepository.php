<?php

namespace App\Repositories;

use App\Models\Municipality;

final class MunicipalitiesRepository extends BaseRepository
{
    public function __construct(Municipality $model)
    {
        parent::__construct($model);
    }
}
